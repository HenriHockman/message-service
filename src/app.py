import sys

from flask import Flask
from flasgger import Swagger
from flask_restful import Api, Resource
from resources.Message import Message
from resources.Main import Main

# Create api
app = Flask(__name__)
app.config.from_object('cfg')

api = Api(app)   

swag = Swagger(app, config=app.config['SWAGGER_CFG'])

# Resources
api.add_resource(Main, '/')
api.add_resource(Message, '/message/<string:messageType>')

if __name__ == '__main__':
  app.run(debug=True, host='0.0.0.0')
