# Question

What are the main HTTP verbs used in REST applications and what are they meant for?

## HTTP Verbs and REST

The main HTTP verbs used in REST are: GET, POST, PUT, PATCH and DELETE. They are usually a map to a CRUD implementation but not only that. Some other verbs like HEAD or OPTIONS are used as well but the are informative. They can be used for inspection, validation, caching information or anything else that you need to get about a resource except for its content.

### GET

It is used to retrieve information from the API. One entity or a set, it only gets the information without data change. It's a readonly resource, commonly cached, depending on the near-static state, expiration or dynamic of the content from the request.

```
https://api.crossknowledgetest.com/user/123
https://api.crossknowledgetest.com/user/list
```

### POST

It is used to create a new entity on the API or to execute some operation on it. It is also used to perform operations like signin, password change and others that require some authentication. It is used for associating a new created child and its parent as well. It is used for non-idempotent operations mainly.

```
https://api.crossknowledgetest.com/user/signup
https://api.crossknowledgetest.com/user/signin
https://api.crossknowledgetest.com/user/123/address
```

### PUT

It is used to update a whole entity, replacing it if needed. It can create a new entity if an ID is provided by the client. It is used for idempotent operations mainly.

```
https://api.crossknowledgetest.com/user/123/update
```

### PATCH

it is used to update parts of an entity, modifying as requested by the client to add, remove, replace or other actions in a propriety from an entity.

```
https://api.crossknowledgetest.com/user/123/phone
```

### DELETE

it is used to remove an entity. Any subsequent attempt to remove any entity that has been deleted before should produce an error.

```
https://api.crossknowledgetest.com/user/123/delete
```
