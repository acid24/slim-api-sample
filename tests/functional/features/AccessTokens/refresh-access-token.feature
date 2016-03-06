Feature: Refresh access token
  As an API client
  I want to be able to refresh my current API access token
  So that I can continue to access restricted endpoints of the API without having to exchange my user credentials for another API access token

Scenario: Refresh access token
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And I provide my current API access token in the request body
  When I receive the response
  Then the status code should be 200
  And the "data.token" property inside the response body should contain a valid API access token with an extended expiration time
  And the "data.expiresAt" property inside the response body should contain a valid future unix timestamp

Scenario: Missing current access token in the input
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And the request body is:
  """
  {}
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

Scenario: Missing current access token in the input
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And the request body is:
  """
  { "currentToken": "" }
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000009"

Scenario:
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And I provide an invalid API access token in the request body
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000103"

Scenario: Missing Content-Type header
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And the "Content-Type" header is missing
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000004"

Scenario: Bad Content-Type header
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And the request media type is "text/plain"
  When I receive the response
  Then the status code should be 415
  And the error code inside the response body should be "ERR-000005"

Scenario: Malformed JSON inside the request body
  Given I make a "POST" request to the "/tokens/actions/refresh" endpoint
  And the request body is:
  """
  Malformed JSON
  """
  When I receive the response
  Then the status code should be 400
  And the error code inside the response body should be "ERR-000006"

Scenario Outline: GET and DELETE requests not allowed
  Given I make a "<method>" request to the "/tokens/actions/refresh" endpoint
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"

Examples:
  | method |
  | GET    |
  | DELETE |

Scenario: PUT requests not allowed
  Given I make a "PUT" request to the "/tokens/actions/refresh" endpoint
  And the request body is:
  """
  { "something" : "sumtin" }
  """
  When I receive the response
  Then the status code should be 405
  And the error code inside the response body should be "ERR-000002"