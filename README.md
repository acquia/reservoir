# How to install Reservoir

[@todo composer instructions here!]


# Authentication

Reservoir supports only OAuth2, and only the Password Grant for OAuth2. Why
only this grant type? Because Reservoir provides a content repository, and
all content must have an author.

The password grant type allows clients (apps/front ends) to interact with
Reservoir, but to always do so on behalf of a user.


# Getting started: demo content & users

During installation, Reservoir creates 2 pieces of demo material:
1. /node/1 -> demo content, titled "Hello world"
2. /user/2 -> demo client user, called  `demo-user`


# Future

- Support GraphQL once it matures: <https://www.drupal.org/project/graphql>
- Add authentication information to the OpenAPI docs: <https://www.drupal.org/node/2886726
