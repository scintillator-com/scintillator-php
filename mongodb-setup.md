#Indexes
```js
db.generators.createIndex({ "library": 1, "language": 1 }, { "name": "library_language", "unique": true })


db.moments.createIndex({ "request.created": 1 }, { "name": "request.created", "sparse": true })
db.moments.createIndex({ "request.method":  1 }, { "name": "request.method",  "sparse": true })
db.moments.createIndex({ "request.scheme":  1 }, { "name": "request.scheme",  "sparse": true })
db.moments.createIndex({ "request.host":    1 }, { "name": "request.host",    "sparse": true })
db.moments.createIndex({ "request.path":    1 }, { "name": "request.path",    "sparse": true })
db.moments.createIndex({ "request.content_length": 1 }, { "name": "request.content_length", "sparse": true })
db.moments.createIndex({ "request.content_type":   1 }, { "name": "request.content_type",   "sparse": true })
db.moments.createIndex({ "response.content_length": 1 }, { "name": "response.content_length", "sparse": true })
db.moments.createIndex({ "response.content_type":   1 }, { "name": "response.content_type",   "sparse": true })
db.moments.createIndex({ "response.status_code":    1 }, { "name": "response.status_code",    "sparse": true })


db.orgs.createIndex({ "name":       1 }, { "name": "name",       "unique": true })
db.orgs.createIndex({ "client_key": 1 }, { "name": "client_key", "unique": true })


db.plans.createIndex({ "name": 1 }, { "name": "name", "unique": true })


db.projects.createIndex({ "org_id": 1, "host": 1 }, { "name": "org_host", "unique": true })


db.rate_limits.createIndex({ "org_id" :         1 }, { "name": "org_id",         "unique": true })
db.rate_limits.createIndex({ "org_client_key" : 1 }, { "name": "org_client_key", "unique": true })


db.sessions.createIndex({ "created": 1 }, { "name": "created" })
db.sessions.createIndex({ "expires": 1 }, { "name": "expires" })
db.sessions.createIndex({ "org_id":  1 }, { "name": "org_id", "sparse": true })
db.sessions.createIndex({ "token":   1 }, { "name": "token",  "unique": true })
db.sessions.createIndex({ "user_id": 1 }, { "name": "user_id" })


db.snippets.createIndex({ "moment_id": 1 }, { "name": "moment_id" })


db.users.createIndex({ "client_key": 1 }, { "name": "client_key", "unique": true })
db.users.createIndex({ "email":      1 }, { "name": "email",      "unique": true })
db.users.createIndex({ "username":   1 }, { "name": "username",   "unique": true })
```


#Generators
```js
db.generators.insert([
{
    "platforms": [ "Node.js", "browser" ],
    "language": "JS",
    "library": "Axios",
    "versions": [ "async", "promise" ]
},
{
    "platforms": [ "browser" ],
    "language": "JS",
    "library": "Fetch",
    "versions": [ "async", "promise" ]
},
{
    "platforms": ["browser"],
    "language": "JS",
    "library": "XHR",
    "versions": [ "async", "promise" ]
},
{
    "platforms": [],
    "language": "Python",
    "library": "Requests",
    "versions": []
},
{
    "platforms": [],
    "language": "PHP",
    "library": "Curl",
    "versions": []
}
])
```

#Plans
```js
db.plans.insert([
{
    "is_enabled" : true,
    "name" : "anonymous",
    "projects" : 0,
    "moments" : {
        "history_days" : 0
    },
    "proxy_ratelimit" : {
        "init" : 6,
        "max" : 6,
        "type" : "scheduled",
        "scheduled" : {
            "increment" : 1,
            "times" : [ 
                [ 
                    0, 
                    "*", 
                    "*", 
                    "*", 
                    "*"
                ]
            ]
        }
    }
},
{
    "is_enabled" : true,
    "name" : "free",
    "projects" : 1,
    "moments" : {
        "history_days" : 3
    },
    "proxy_ratelimit" : {
        "init" : 10,
        "max" : 24,
        "type" : "scheduled",
        "scheduled" : {
            "increment" : 1,
            "times" : [ 
                [ 
                    0, 
                    "*", 
                    "*", 
                    "*", 
                    "*"
                ]
            ]
        }
    }
},
{
    "is_enabled" : true,
    "name" : "basic",
    "projects" : 1,
    "moments" : {
        "history_days" : 30
    },
    "proxy_ratelimit" : {
        "init" : 96,
        "max" : 96,
        "type" : "scheduled",
        "scheduled" : {
            "increment" : 4,
            "times" : [ 
                [ 
                    0, 
                    "*", 
                    "*", 
                    "*", 
                    "*"
                ]
            ]
        }
    }
}
])
```