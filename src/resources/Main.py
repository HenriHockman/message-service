from flask_restful import Resource 

class Main(Resource):
  def get(self):
    """
    Display information where to find api documentation in the root url
    ---
    tags:
      - Main
    responses:
      200:
        description: Instruction to find apidoc
    """
    return 'Message Service documentation can be found in /api/doc', 200
