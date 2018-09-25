# for debug: print(something_to_print, file=sys.stderr)
import sys

from flask_restful import Resource, abort, reqparse
from handlers.Sender import Sender

class Message(Resource):
  def post(self, messageType):
    """
    Endpoint to send message to queue.
    ---
    tags:
      - Message
    consumes:
      - application/json
    parameters:
      - in: path
        name: messageType
        enum: ['sms', 'email - not yet implemented']
        description: Type of message, either SMS or email
        type: string
      - in: body
        name: body
        schema:
          required:
            - receivers
            - sender
            - client
            - content
          properties:
            receivers:
              type: array
              description: List of phone numbers/emails to receive the message.
              items:
                type: string
            sender:
              type: string
              description: Phone number/email which will be displayed as message sender.
            client:
              type: string
              description: Client company using the Message Service. This should be automated.
            content:
              type: string
              description: Message content
    responses:
      200:
        description: Message sent successfully to queue
      400:
        description: Missing a required field from message body
    """
    parser = reqparse.RequestParser()
    
    parser.add_argument('receivers', action='append')
    parser.add_argument('content')
    parser.add_argument('client')
    parser.add_argument('sender')
    
    args = parser.parse_args()

    # Check that we have all necessary arguments

    if not(args['receivers']):
      abort(400, message='Missing receiver(s) for message')

    if not(args['content']):
      abort(400, message='Missing content from message')

    if not(args['client']):
      abort(400, message='Missing client from message')

    if not(args['sender']):
      abort(400, message='Missing sender from message')

    # Publish message to queue
    messageSender = Sender()
    
    for receiver in args['receivers']:
      message = {
        'type': messageType,
        'receiver': receiver,
        'content': args['content'],
        'client': args['client'],
        'sender': args['sender']
      }
      messageSender.publish(message)
    
    return 'Message(s) sent to queue.', 200
