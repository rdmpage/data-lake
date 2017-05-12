{
   "_id": "_design/citation_matching",
   "_rev": "6-0bf2d51ce8f7c6dc26e4753ded95dfa6",
   "language": "javascript",
   "views": {
       "no_doi": {
           "map": "function(doc) {\n  if (doc.message) {\n    switch (doc['message-format']) {\n\n      case 'application/vnd.crossref-api-message+json':\n        if (!doc.message.DOI) {\n          emit(doc._id, doc.message);\n        }\n        break;\n\n     case 'application/vnd.crossref-citation+json':\n        if (!doc.message.DOI) {\n          emit(doc._id, doc.message);\n        }\n        break;\n\n     default:\n       break;\n    }\n  }\n}"
       },
       "no_doi_journal": {
           "map": "\n\nfunction(doc) {\n  if (doc.message) {\n    switch (doc['message-format']) {\n\n      case 'application/vnd.crossref-api-message+json':\n        if (!doc.message.DOI) {\n          if (doc.message['container-title']) {\n          var container = '';\n\t  if (Array.isArray(doc.message['container-title'])) {\n\t    container = doc.message['container-title'][0];\n\t  } else {\n\t    container = doc.message['container-title'];\n\t  }\t\n          emit(container, doc.message);\n         }\n        }\n        break;\n\n     case 'application/vnd.crossref-citation+json':\n        if (!doc.message.DOI) {\n          if (doc.message['journal-title']) {\n            emit(doc.message['journal-title'], doc.message);\n          }\n        }\n        break;\n\n     default:\n       break;\n    }\n  }\n}"
       }
   }
}