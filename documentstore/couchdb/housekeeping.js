{
   "_id": "_design/housekeeping",
   "language": "javascript",
   "views": {
       "ids": {
           "map": "\nfunction(doc) {\n  emit(null, doc._id);\n}"
       }
   }
}