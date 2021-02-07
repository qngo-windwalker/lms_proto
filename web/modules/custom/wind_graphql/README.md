# Drupal GraphQL

## Introduction
The instructions on https://drupal-graphql.gitbook.io/graphql/v/8.x-4.x/
miss some critical steps. This Readme will fill missing steps

### Drupal GraphQL 4x

## Quick Start

- Install the module and enable GraphQL. EX `composer require drupal/graphql:4.0.0-beta1`
- Navigate to `/admin/config/graphql` create a new server.
- Create a server and specify an endpoint such as `/graphql`. After creating the server click on `explorer` and this should bring you to the Graphiql explorer.
     - You can use the "Example schema" that comes with the graphql_examples module (comes with the graphql module but needs to be enabled separately) to try out using GraphQL for the first time before making your own schema.
     - Navigate to `admin/config/graphql/servers/manage/graphql_server` and on the "Schema" dropdown, chose `Example schema"`
- Navigate to `/admin/people/permissions` To enable who can control who can perform arbitrary and persisted queries against graphql and also who can access the Voyager or the GraphiQL pages.
To be able to query something you first have to create an Article in the Drupal backend.

### Creating a schema

4. **Read the comments** and then enter the following query in the left pane:

   ```graphql
     query {
       article(id: 1){
         id
         title
       }
     }
   ```

5. Press `Ctrl-Space` and you should see something like the following display in the right pane:

   ```javascript
    {
      "data": {
        "article": {
            "id": 1,
            "title: "Hello GraphQL"
      }
    }
   ```

6. Congratulations!! You just figured out how to execute your first GraphQL query. This query is displaying a list of articles.
### Front-end
How to get the data on the front-end:
```javascript
const query = ` query {
    article(id: 14){
      id
      title
    }
}`;

axios.get(`/graphql/?query=${query}`)
```
Additional info: Rapid Data APIs using GraphQL and Drupal @see https://www.youtube.com/watch?v=uTLPQUb0dx0

**NOTES:**

* The GraphiQL explorer, included with the module, is your friend, it’s amazing. You will most likely use the GraphiQL explorer to build and test more complicated queries.
* GraphQL is introspective, meaning that the entire schema \(data model\) is known up front. This is important as it allows tools like GraphiQL to implement autocompletion.
* You can use GraphiQL to explore your way through the data and configuration, once you know the basic GraphQL syntax. You can use the tab key in the explorer like you would with autocompletion or intellisense in modern IDEs.
