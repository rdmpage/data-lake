{
   "_id": "_design/queue",
   "language": "javascript",
   "views": {
       "todo": {
           "map": "/*\nIf document.message is empty then we need to resolve this \nobject, so place on queue (indexed by message-timestamp). So that \nwe don't get trapped by an object that we can't resolve we keep a record of\nresolution attempts and if this is greater than a limit\nwe don't place the object on the queue.\n*/\nfunction(doc) {\n  var max_resolution_attempts = 2;\n  if (doc['message-timestamp']) {\n    if (doc.message) {\n    } else {\n       var attempts = 0;\n       \n       if (!(doc['message-attempts'] === undefined)) {\n        attempts = doc['message-attempts'];\n       }\n      \n      if (attempts < max_resolution_attempts) \n      {\n        emit(doc['message-timestamp'], doc._id);\n      }\n    }\n  }\n}"
       },
       "failed_to_resolve": {
           "map": "/*\nList those objects that we haven't managed to resolve.\n*/\nfunction(doc) {\n  var max_resolution_attempts = 2;\n  if (doc['message-timestamp']) {\n    if (!doc.message) {\n      var attempts = 0;\n      if (doc['message-attempts']) {\n        attempts = doc['message-attempts'];\n      }\n      if (attempts >= max_resolution_attempts) {\n        emit(doc['message-timestamp'], doc._id);\n      }\n    }\n  }\n}"
       }
   }
}