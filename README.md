![Reservoir - Drupal distribution](https://raw.githubusercontent.com/acquia/reservoir/8.x-1.x/reservoir-logo.png)

# Reservoir
Reservoir is a back end for your front end: a content repository. Uses
[JSON API](http://jsonapi.org) and OAuth2.

It's dead simple to use. Log in and there are four tabs:

1. **Content:** where content administrators administer content
2. **API:** where client developers can browse the API documentation
3. **Content models:** where the owner can model content for their needs
4. **Access control:** where the owner can administer users, clients, roles,
   permissions and tokens

After installing, you're welcomed by a tour, and you're ready to explore — in
fact, you can make API requests right away!

---

## How to install Reservoir

The preferred way to install Reservoir is using our
[Composer-based project template][template]. It's easy!

```
$ composer create-project acquia/reservoir-project MY_PROJECT
```

## Concepts

There are only seven concepts you need to understand, and most of them you already know!

1. Content models
2. Content
3. Users
4. Clients (OAuth2)
5. Roles
6. Permissions
7. Tokens (OAuth2)

The tour starts automatically after installing Reservoir. Afterwards, you can
take the tour again by clicking the "Tour" button in the top right corner.

## Authentication

Reservoir supports only OAuth2, and only the Password Grant for OAuth2. Why
only this grant type? Because Reservoir provides a content repository, and
all content must have an author.

The password grant type allows clients (applications and front ends) to
interact with Reservoir, but always on behalf of a user.

## Before deploying to production

Before deploying Reservoir to production, delete demo material and change the
keys.

During installation, Reservoir creates four pieces of demo material:
1. `/node/1` -> demo content, titled "Hello world"
2. `/user/2` -> demo client user, called  `demo-user`
3. `/user/3` -> demo content administrator, called `demo-writer`
4. `/user/4` -> demo client developer, called `demo-developer`
5. `/client/1` -> demo client, called `Demo app`

(The password is identical to the user upon each installation.)

You'll want to:
1. Remove the demo material
2. Replace the auto-generated OAuth2 public/private key pair
3. Refine CORS settings

That's it!

## Future

- Support [GraphQL](https://www.drupal.org/project/graphql) once it matures
- [Add authentication information to the OpenAPI docs](https://www.drupal.org/node/2886726)
- Add maintainable mechanism to not expose certain content/config entity types in both API docs nor allow accessing them via JSON API.
- Ensure only users with the `content_administrator` or `client_developer` roles can log in.
- Allow users with the `client_developer` to create and edit roles other than `client_administrator` and `client_developer`, and allow them only to grant+revoke non-restricted permissions. This then allows them to define roles (scopes) for clients.
- Make it easy to delete all default content in one go: node 1, users 2, 3 and 4.

[template]: https://github.com/acquia/reservoir-project "Composer-based project template"
