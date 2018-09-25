MQ_USER = 'admin'
MQ_PASSWORD = 'admin'
MQ_HOST = 'localhost'
MQ_PORT = 5672

SWAGGER = {
  'title': 'Message Service API documentation',
  'uiversion': 3
}

SWAGGER_CFG = {
  "headers": [],
  "specs": [
    {
      "endpoint": 'apispec_1',
      "route": '/apispec_1.json',
      "rule_filter": lambda rule: True,  # all in
      "model_filter": lambda tag: True,  # all in
    }
  ],
  "static_url_path": "/flasgger_static",
  # "static_folder": "static",  # must be set by user
  # "swagger_ui": True,
  "specs_route": "/api/doc/"
}
