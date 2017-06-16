# How to install Reservoir

[@todo composer instructions here!]


# Authentication

Reservoir supports only OAuth2, and only the Password Grant for OAuth2. Why
only this grant type? Because Reservoir provides a content repository, and
all content must have an author.

The password grant type allows clients (apps/front ends) to interact with
Reservoir, but to always do so on behalf of a user.


# Getting started: demo content & users

During installation, Reservoir creates 4 pieces of demo material:
1. /node/1 -> demo content, titled "Hello world"
2. /user/2 -> demo client user, called  `demo-user`
3. /user/3 -> demo content administrator, called `demo-writer`
4. /user/4 -> demo client developer, called `demo-developer`
5. /client/1 -> demo client, called `Demo app`

(The password is each time identical to the user.)


# Future

- Support GraphQL once it matures: <https://www.drupal.org/project/graphql>
- Add authentication information to the OpenAPI docs: <https://www.drupal.org/node/2886726
- Add maintainable mechanism to not expose certain content/config entity types in both API docs nor allow accessing them via JSON API.
- Ensure only users with the 'content_administrator' or 'client_developer' roles can log in.
- Allow users with the 'client_developer' to create and edit roles other than 'client_administrator' and 'client_developer', and allow them only to grant+revoke non-restricted permissions. This then allows them to define roles (scopes) for clients.
- Make it easy to delete all default content in one go: node 1, users 2, 3 and 4.
