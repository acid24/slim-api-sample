Feature: Issue access token in exchange for user credentials
  As an API client
  I want to exchange my user credentials for an API token
  So that I can access restricted endpoints of the API

@createsTestUser
Scenario: Issue access token in exchange for user credentials
  Given there is a user in database with username "behat" and password "behat"
  And I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "username": "behat", "password": "behat" }
  """
  When I receive the response
  Then the status code should be 200
  And the "data.token" property inside the response body should contain a valid API access token
  And the "data.expiresAt" property inside the response body should contain a valid future unix timestamp

Scenario Outline: Missing/invalid fields in the input JSON
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  <body>
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

  Examples:
    | body                                   |
    | { "user": "test", "password": "test" } |
    | { "username": "test", "pass": "test" } |
    | { "username": "", "password": "test" } |
    | { "username": "test", "password": "" } |

Scenario: Missing Content-Type header
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the "Content-Type" header is missing
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000004"

Scenario: Bad Content-Type header
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request media type is "text/plain"
  When I receive the response
  Then the status code should be 415
  And the error code inside the response body should be "ERR-000005"

Scenario: Malformed JSON inside the request body
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  Malformed JSON
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000006"

Scenario Outline: GET and DELETE requests not allowed
  Given I make a "<method>" request to the "/tokens/actions/issue" endpoint
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"

  Examples:
    | method |
    | GET    |
    | DELETE |

Scenario: PUT requests not allowed
  Given I make a "PUT" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "something" : "sumtin" }
  """
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"