Feature: Issue access token in exchange for user credentials
  As an API client
  I want to exchange my user credentials for an API token
  So that I can access restricted endpoints of the API

Scenario: Issue access token in exchange for user credentials
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "username": "test", "password": "password" }
  """
  When I receive the response
  Then the status code should be 200
  And the "data.token" property inside the response body should contain a valid API access token
  And the "data.expiresAt" property inside the response body should contain a valid future unix timestamp

Scenario: Missing username in the input
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "user": "test", "password": "test" }
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

Scenario: Missing password in the input
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "username": "test", "pass": "test" }
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

Scenario: Empty username in the input
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "username": "", "password": "test" }
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

Scenario: Empty password in the input
  Given I make a "POST" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "username": "test", "password": "" }
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

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

Scenario: GET requests not allowed
  Given I make a "GET" request to the "/tokens/actions/issue" endpoint
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"

Scenario: DELETE requests not allowed
  Given I make a "DELETE" request to the "/tokens/actions/issue" endpoint
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"

Scenario: PUT requests not allowed
  Given I make a "PUT" request to the "/tokens/actions/issue" endpoint
  And the request body is:
  """
  { "something" : "sumtin" }
  """
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"