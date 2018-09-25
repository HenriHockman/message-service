# for debug: print(something_to_print, file=sys.stderr)
import sys

import pika
import json
from flask import current_app as app

class Sender(object):
  
  def publish(self, message):
    credentials = pika.PlainCredentials(app.config["MQ_USER"], app.config["MQ_PASSWORD"])
    
    connection = pika.BlockingConnection(
      pika.ConnectionParameters(
        app.config["MQ_HOST"],
        app.config["MQ_PORT"],
        '/',
        credentials
      ))

    channel = connection.channel()

    channel.exchange_declare(
      exchange='messages',
      exchange_type='topic',
      durable='true'
    )

    messageType = message['type']
    clientApplication = 'default' # TODO: Change this when implementing messaging service to applications (possibly get from authentication?)
    client = message['client']
    
    message['clientApp'] = clientApplication

    routingKey = "{0}.{1}.{2}".format(messageType, clientApplication, client)
    strippedRoutingKey = ''.join(routingKey.split())

    channel.basic_publish(exchange='messages',
                          routing_key=strippedRoutingKey,
                          body=json.dumps(message),
                          properties=pika.BasicProperties(
                            delivery_mode = 2, # Make message persistent
                          ))
    
    connection.close()
