{
   "_id": "_design/citations",
   "language": "javascript",
   "views": {
       "journal": {
           "map": "function(doc) {\n  if (doc['message-format']) {\n    if (doc['message-format'] == 'application/vnd.crossref-api-message+json') {\n       if (doc.message.reference) {\n          for (var i in doc.message.reference) {\n            if (doc.message.reference[i]['journal-title']) {\n              emit(doc.message.reference[i]['journal-title'], doc.message.reference[i]);\n            }\n          }\n       }   \n    }\n  }\n}"
       },
       "hash": {
           "map": "function(doc) {\n  if (doc['message-format']) {\n    if (doc['message-format'] == 'application/vnd.crossref-api-message+json') {\n       if (doc.message.reference) {\n          for (var i in doc.message.reference) {\n            var hash = [];\n\n       if (doc.message.reference[i].year) {\n          year = doc.message.reference[i].year;\n          year = year.replace(/[a-z]/g, '');\n          hash.push(year);\n        }\n\n        if (doc.message.reference[i].volume) {\n          hash.push(doc.message.reference[i].volume);\n        }\n\n        if (doc.message.reference[i]['first-page']) {\n          hash.push(doc.message.reference[i]['first-page']);\n        }\n\n\n    if (hash.length == 3) {\n      emit(hash, 1);\n}\n          }\n       }   \n    }\n  }\n}\n",
           "reduce": "_sum"
       },
       "unstructured": {
           "map": "function(doc) {\n  if (doc['message-format']) {\n    if (doc['message-format'] == 'application/vnd.crossref-api-message+json') {\n       if (doc.message.reference) {\n          for (var i in doc.message.reference) {\n            if (doc.message.reference[i].unstructured) {\n              emit(doc._id, doc.message.reference[i].unstructured);\n            }\n          }\n       }   \n    }\n  }\n}"
       },
       "citations": {
           "map": "function(doc) {\n  if (doc['message-format']) {\n    if (doc['message-format'] == 'application/vnd.crossref-api-message+json') {\n       if (doc.message.reference) {\n          for (var i in doc.message.reference) {\n            emit(doc._id, doc.message.reference[i]);\n          }\n       }   \n    }\n  }\n}"
       }
   }
}