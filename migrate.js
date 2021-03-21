db.plans.insert([
{
    "_id" : ObjectId("5fe7624e184c221d62c8b548"),
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
    "_id" : ObjectId("5fe76259184c221d62c8b555"),
    "is_enabled" : true,
    "name" : "free",
    "projects" : 1,
    "moments" : {
        "history_days" : 3
    },
    "proxy_ratelimit" : {
        "init" : 10000,
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
    "_id" : ObjectId("5fe76266184c221d62c8b574"),
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