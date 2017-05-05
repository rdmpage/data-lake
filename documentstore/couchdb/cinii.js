{
   "_id": "_design/cinii",
   "language": "javascript",
   "lists": {
       "n-triples": "function(head,req) { var headers = ''; var row; start({ 'headers': { 'Content-Type': 'text/plain' } }); while(row = getRow()) { send(row.value); } }"
   },
   "views": {
       "nt": {
           "map": "/*\n\nShared code\n\n\n*/\n//----------------------------------------------------------------------------------------\n// Store a triple with optional language code\nfunction triple(subject, predicate, object, language) {\n  var triple = [];\n  triple[0] = subject;\n  triple[1] = predicate;\n  triple[2] = object;\n\n  if (typeof language === 'undefined') {} else {\n    triple[3] = language;\n  }\n\n  return triple;\n}\n\n//----------------------------------------------------------------------------------------\n// Store a quad (not used at present)\nfunction quad(subject, predicate, object, context) {\n  var triple = [];\n  triple[0] = $subject;\n  triple[1] = $predicate;\n  triple[2] = $object;\n  triple[3] = $context;\n\n  return triple;\n}\n\n//----------------------------------------------------------------------------------------\n// Enclose triple in suitable wrapping for HTML display or triplet output\nfunction wrap(s, html) {\n  if (s.match(/^(http|urn|_:)/)) {\n    s = s.replace(/\\\\_/g, '_');\n\n    // handle < > in URIs such as SICI-based DOIs\n    s = s.replace(/</g, '%3C');\n    s = s.replace(/>/g, '%3E');\n\n   if (html) {\n      s = '&lt;' + s + '&gt;';\n    } else {\n      s = '<' + s + '>';\n    }\n  } else {\n    s = '\"' + s.replace(/\"/g, '\\\\\"') + '\"';\n  }\n  return s;\n}\n\n//----------------------------------------------------------------------------------------\n// https://css-tricks.com/snippets/javascript/htmlentities-for-javascript/\nfunction htmlEntities(str) {\n  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;');\n}\n\n//----------------------------------------------------------------------------------------\nfunction output(doc, triples) {\n    // CouchDB\n    for (var i in triples) {\n      var s = 0;\n      var p = 1;\n      var o = 2;\n\n      var lang = 3;\n\n      var nquads = wrap(triples[i][s], false) \n\t    \t+ ' ' + wrap(triples[i][p], false) \n\t    \t+ ' ' + wrap(triples[i][o], false);\n\t    if (triples[i][lang]) {\n\t    \tnquads += '@' + triples[i][lang];\n\t    }\n\t    \t\n\t    nquads += ' .' + \"\\n\";\n\n\n      emit(doc._id, nquads);\n    }\n}\n\n/*\nxml2json v 1.1\ncopyright 2005-2007 Thomas Frank\n\nThis program is free software under the terms of the \nGNU General Public License version 2 as published by the Free \nSoftware Foundation. It is distributed without any warranty.\n*/\n\nxml2json={\n\tparser:function(xmlcode,ignoretags,debug){\n\t\tif(!ignoretags){ignoretags=\"\"};\n\t\txmlcode=xmlcode.replace(/\\s*\\/>/g,'/>');\n\t\txmlcode=xmlcode.replace(/<\\?[^>]*>/g,\"\").replace(/<\\![^>]*>/g,\"\");\n\t\tif (!ignoretags.sort){ignoretags=ignoretags.split(\",\")};\n\t\tvar x=this.no_fast_endings(xmlcode);\n\t\tx=this.attris_to_tags(x);\n\t\tx=escape(x);\n\t\tx=x.split(\"%3C\").join(\"<\").split(\"%3E\").join(\">\").split(\"%3D\").join(\"=\").split(\"%22\").join(\"\\\"\");\n\t\tfor (var i=0;i<ignoretags.length;i++){\n\t\t\tx=x.replace(new RegExp(\"<\"+ignoretags[i]+\">\",\"g\"),\"*$**\"+ignoretags[i]+\"**$*\");\n\t\t\tx=x.replace(new RegExp(\"</\"+ignoretags[i]+\">\",\"g\"),\"*$***\"+ignoretags[i]+\"**$*\")\n\t\t};\n\t\tx='<JSONTAGWRAPPER>'+x+'</JSONTAGWRAPPER>';\n\t\tthis.xmlobject={};\n\t\tvar y=this.xml_to_object(x).jsontagwrapper;\n\t\tif(debug){y=this.show_json_structure(y,debug)};\n\t\treturn y\n\t},\n\txml_to_object:function(xmlcode){\n\t\tvar x=xmlcode.replace(/<\\//g,\"�\");\n\t\tx=x.split(\"<\");\n\t\tvar y=[];\n\t\tvar level=0;\n\t\tvar opentags=[];\n\t\tfor (var i=1;i<x.length;i++){\n\t\t\tvar tagname=x[i].split(\">\")[0];\n\t\t\t\n\t\t\t//tagname = tagname.replace(/(:|%3A)/i, '_colon_');\n\t\t\t//console.log(tagname);\n\t\t\t\n\t\t\topentags.push(tagname);\n\t\t\tlevel++\n\t\t\ty.push(level+\"<\"+x[i].split(\"�\")[0]);\n\t\t\twhile(x[i].indexOf(\"�\"+opentags[opentags.length-1]+\">\")>=0){level--;opentags.pop()}\n\t\t};\n\t\tvar oldniva=-1;\n\t\tvar objname=\"this.xmlobject\";\n\t\tfor (var i=0;i<y.length;i++){\n\t\t\tvar preeval=\"\";\n\t\t\tvar niva=y[i].split(\"<\")[0];\n\t\t\tvar tagnamn=y[i].split(\"<\")[1].split(\">\")[0];\n\t\t\ttagnamn=tagnamn.toLowerCase();\n\t\t\t\n\t\t\t//tagnamn = tagnamn.replace(/(:|%3A)/i, '_colon_');\n\t\t\t\n\t\t\tvar rest=y[i].split(\">\")[1];\n\t\t\tif(niva<=oldniva){\n\t\t\t\tvar tabort=oldniva-niva+1;\n\t\t\t\tfor (var j=0;j<tabort;j++){objname=objname.substring(0,objname.lastIndexOf(\".\"))}\n\t\t\t};\n\t\t\tobjname+=\".\"+tagnamn;\n\t\t\tvar pobject=objname.substring(0,objname.lastIndexOf(\".\"));\n\t\t\tif (eval(\"typeof \"+pobject) != \"object\"){preeval+=pobject+\"={value:\"+pobject+\"};\\n\"};\n\t\t\tvar objlast=objname.substring(objname.lastIndexOf(\".\")+1);\n\t\t\tvar already=false;\n\t\t\tfor (k in eval(pobject)){if(k==objlast){already=true}};\n\t\t\tvar onlywhites=true;\n\t\t\tfor(var s=0;s<rest.length;s+=3){\n\t\t\t\tif(rest.charAt(s)!=\"%\"){onlywhites=false}\n\t\t\t};\n\t\t\tif (rest!=\"\" && !onlywhites){\n\t\t\t\tif(rest/1!=rest){\n\t\t\t\t\trest=\"'\"+rest.replace(/\\'/g,\"\\\\'\")+\"'\";\n\t\t\t\t\trest=rest.replace(/\\*\\$\\*\\*\\*/g,\"</\");\n\t\t\t\t\trest=rest.replace(/\\*\\$\\*\\*/g,\"<\");\n\t\t\t\t\trest=rest.replace(/\\*\\*\\$\\*/g,\">\")\n\t\t\t\t}\n\t\t\t} \n\t\t\telse {rest=\"{}\"};\n\t\t\tif(rest.charAt(0)==\"'\"){rest='unescape('+rest+')'};\n\t\t\tif (already && !eval(objname+\".sort\")){preeval+=objname+\"=[\"+objname+\"];\\n\"};\n\t\t\tvar before=\"=\";after=\"\";\n\t\t\tif (already){before=\".push(\";after=\")\"};\n\t\t\tvar toeval=preeval+objname+before+rest+after;\n\t\t\t\n\t\t\t//console.log('toeval='+toeval);\n\t\t\t\n\t\t\teval(toeval);\n\t\t\tif(eval(objname+\".sort\")){objname+=\"[\"+eval(objname+\".length-1\")+\"]\"};\n\t\t\toldniva=niva\n\t\t};\n\t\treturn this.xmlobject\n\t},\n\tshow_json_structure:function(obj,debug,l){\n\t\tvar x='';\n\t\tif (obj.sort){x+=\"[\\n\"} else {x+=\"{\\n\"};\n\t\tfor (var i in obj){\n\t\t\tif (!obj.sort){x+=i+\":\"};\n\t\t\tif (typeof obj[i] == \"object\"){\n\t\t\t\tx+=this.show_json_structure(obj[i],false,1)\n\t\t\t}\n\t\t\telse {\n\t\t\t\tif(typeof obj[i]==\"function\"){\n\t\t\t\t\tvar v=obj[i]+\"\";\n\t\t\t\t\t//v=v.replace(/\\t/g,\"\");\n\t\t\t\t\tx+=v\n\t\t\t\t}\n\t\t\t\telse if(typeof obj[i]!=\"string\"){x+=obj[i]+\",\\n\"}\n\t\t\t\telse {x+=\"'\"+obj[i].replace(/\\'/g,\"\\\\'\").replace(/\\n/g,\"\\\\n\").replace(/\\t/g,\"\\\\t\").replace(/\\r/g,\"\\\\r\")+\"',\\n\"}\n\t\t\t}\n\t\t};\n\t\tif (obj.sort){x+=\"],\\n\"} else {x+=\"},\\n\"};\n\t\tif (!l){\n\t\t\tx=x.substring(0,x.lastIndexOf(\",\"));\n\t\t\tx=x.replace(new RegExp(\",\\n}\",\"g\"),\"\\n}\");\n\t\t\tx=x.replace(new RegExp(\",\\n]\",\"g\"),\"\\n]\");\n\t\t\tvar y=x.split(\"\\n\");x=\"\";\n\t\t\tvar lvl=0;\n\t\t\tfor (var i=0;i<y.length;i++){\n\t\t\t\tif(y[i].indexOf(\"}\")>=0 || y[i].indexOf(\"]\")>=0){lvl--};\n\t\t\t\ttabs=\"\";for(var j=0;j<lvl;j++){tabs+=\"\\t\"};\n\t\t\t\tx+=tabs+y[i]+\"\\n\";\n\t\t\t\tif(y[i].indexOf(\"{\")>=0 || y[i].indexOf(\"[\")>=0){lvl++}\n\t\t\t};\n\t\t\tif(debug==\"html\"){\n\t\t\t\tx=x.replace(/</g,\"&lt;\").replace(/>/g,\"&gt;\");\n\t\t\t\tx=x.replace(/\\n/g,\"<BR>\").replace(/\\t/g,\"&nbsp;&nbsp;&nbsp;&nbsp;\")\n\t\t\t};\n\t\t\tif (debug==\"compact\"){x=x.replace(/\\n/g,\"\").replace(/\\t/g,\"\")}\n\t\t};\n\t\treturn x\n\t},\n\tno_fast_endings:function(x){\n\t\tx=x.split(\"/>\");\n\t\tfor (var i=1;i<x.length;i++){\n\t\t\tvar t=x[i-1].substring(x[i-1].lastIndexOf(\"<\")+1).split(\" \")[0];\n\t\t\tx[i]=\"></\"+t+\">\"+x[i]\n\t\t}\t;\n\t\tx=x.join(\"\");\n\t\treturn x\n\t},\n\tattris_to_tags: function(x){\n\t\tvar d=' =\"\\''.split(\"\");\n\t\tx=x.split(\">\");\n\t\tfor (var i=0;i<x.length;i++){\n\t\t\tvar temp=x[i].split(\"<\");\n\t\t\tfor (var r=0;r<4;r++){temp[0]=temp[0].replace(new RegExp(d[r],\"g\"),\"_jsonconvtemp\"+r+\"_\")};\n\t\t\tif(temp[1]){\n\t\t\t\ttemp[1]=temp[1].replace(/'/g,'\"');\n\t\t\t\ttemp[1]=temp[1].split('\"');\n\t\t\t\tfor (var j=1;j<temp[1].length;j+=2){\n\t\t\t\t\tfor (var r=0;r<4;r++){temp[1][j]=temp[1][j].replace(new RegExp(d[r],\"g\"),\"_jsonconvtemp\"+r+\"_\")}\n\t\t\t\t};\n\t\t\t\ttemp[1]=temp[1].join('\"')\n\t\t\t};\n\t\t\tx[i]=temp.join(\"<\")\n\t\t};\n\t\tx=x.join(\">\");\n\t\tx=x.replace(/ ([^=]*)=([^ |>]*)/g,\"><$1>$2</$1\");\n\t\tx=x.replace(/>\"/g,\">\").replace(/\"</g,\"<\");\n\t\tfor (var r=0;r<4;r++){x=x.replace(new RegExp(\"_jsonconvtemp\"+r+\"_\",\"g\"),d[r])}\t;\n\t\treturn x\n\t}\n};\n\n\nif(!Array.prototype.push){\n\tArray.prototype.push=function(x){\n\t\tthis[this.length]=x;\n\t\treturn true\n\t}\n};\n\nif (!Array.prototype.pop){\n\tArray.prototype.pop=function(){\n  \t\tvar response = this[this.length-1];\n  \t\tthis.length--;\n  \t\treturn response\n\t}\n};\n\n\n// http://stackoverflow.com/a/17076120\nfunction decodeHTMLEntities(text) {\n   var entities = [\n        ['amp', '&'],\n        ['apos', '\\''],\n        ['#x27', '\\''],\n        ['#x2F', '/'],\n        ['#39', '\\''],\n\t['#039', '\\''],\n        ['#47', '/'],\n        ['lt', '<'],\n        ['gt', '>'],\n        ['nbsp', ' '],\n        ['quot', '\"']\n    ];\n\n    text = String(text);\n\n    for (var i = 0, max = entities.length; i < max; ++i) {\n        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);\n    }\n\n    return text;\n}\n\nfunction  detect_language(s) {\n  var language = null;\n  var matched = 0;\n  var parts =[];\n  \n  var regexp = [];\n  \n  // https://gist.github.com/ryanmcgrath/982242\n  regexp['ja'] = /[\\u3000-\\u303F]|[\\u3040-\\u309F]|[\\u30A0-\\u30FF]|[\\uFF00-\\uFFEF]|[\\u4E00-\\u9FAF]|[\\u2605-\\u2606]|[\\u2190-\\u2195]|\\u203B/g; \n  // http://hjzhao.blogspot.co.uk/2015/09/javascript-detect-chinese-character.html\n  regexp['zh'] = /[\\u4E00-\\uFA29]/g; \n  // http://stackoverflow.com/questions/32709687/js-check-if-string-contains-only-cyrillic-symbols-and-spaces\n  regexp['ru'] = /[\\u0400-\\u04FF]/g; \n  \n  for (var i in regexp) {\n    parts = s.match(regexp[i]);\n    \n\t  if (parts != null) {\n\t\tif (parts.length > matched) {\n\t\t  language = i;\n\t\t  matched = parts.length;\n\t\t}\n\t  }\n  }\n  \n  // require a minimum matching\n  if (matched < 2) {\n    language = null;\n  }\n  \n  return language;\n  \n}\n\n\n\n//----------------------------------------------------------------------------------------\n// Smple key-value store to check that we don't emit duplicate triples\nvar kv_store = {};\n\nfunction have_value_already(key, value) {\n   var found = false;\n   \n   if (!kv_store[key]) {\n     kv_store[key] = [];\n     kv_store[key].push(value);\n   } else {\n     if (kv_store[key].indexOf(value) === -1) {\n        kv_store[key].push(value);\n     } else {\n       found = true;\n     }\n   }\n   \n   return found;\n}\n\n\nfunction message(doc) {\n\n  var subject_id = doc._id;\n  var triples = [];\n  var type = '';\n\n  var xml = doc.message.xml;\n\n  // administrivia\n  xml = xml.replace(/<\\?xml version=\"1.0\" encoding=\"UTF-8\"\\?>\\s*/i, '');\n  xml = xml.replace(/<\\?xml-stylesheet type=\"text\\/xsl\" href=\"lsid.rdf.xsl\"\\?>\\s*/, '');\n\n  // namespaces\n  xml = xml.replace(/<rdf:RDF\\s+(xmlns:\\w+=([a-zA-Z0-9:\\/\"\\.#-]+)\"\\s*)+?>/, '<rdf:RDF>');\n\n  // can't use colons\n  xml = xml.replace(/<(\\w+):(\\w+)/gm, '<$1$2');\n  xml = xml.replace(/<\\/(\\w+):(\\w+)>/gm, '</$1$2>');\n\n  // attributes that we missed\n  xml = xml.replace(/dc:/gm, 'dc');\n  xml = xml.replace(/rdf:/gm, 'rdf');\n  xml = xml.replace(/tm:/gm, 'tm');\n  xml = xml.replace(/xml:/gm, 'xml');\n\n  // hack to handle language code in foafmaker that can cause problems\n  // converting XML to JSON\n  xml = xml.replace(/foafname xmllang=\"en\"/gm, 'foafname');\n\n  //alert(xml);\n\n  var json = xml2json.parser(xml);\n\n  //alert(JSON.stringify(json, null, 2));\n\n  if (json.rdfrdf) {\n\n    triples.push(triple(subject_id,\n      'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n      'http://schema.org/ScholarlyArticle'));\n\n    triples.push(triple(subject_id,\n      'http://schema.org/identifier',\n      subject_id));\n\n    for (var s in json.rdfrdf.rdfdescription) {\n      for (var k in json.rdfrdf.rdfdescription[s]) {\n\n        //console.log(k);\n\n        if (typeof json.rdfrdf.rdfdescription[s][k] === 'object') {\n          var value = json.rdfrdf.rdfdescription[s][k];\n\n          //console.log(JSON.stringify(value));\n\n          switch (k) {\n\n            case 'foafdepiction':\n              triples.push(triple(subject_id,\n                'http://schema.org/thumbnailUrl',\n                value.foafimage.rdfabout));\n              break;\n\n              // link to journal, CiNii uses its own identifier (which can be resolved to RDF)\t\t\t\t\t\n            case 'dctermsispartof':\n              var journal_id = value.rdfresource;\n              triples.push(triple(subject_id,\n                'http://schema.org/isPartOf',\n                journal_id));\n\n              triples.push(triple(journal_id,\n                'http://schema.org/identifier',\n                journal_id));\n\n              triples.push(triple(journal_id,\n                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n                'http://schema.org/Periodical'));\n\n              if (value.dctitle) {\n\n                if (!have_value_already('journal', value.dctitle)) {\n                  triples.push(triple(journal_id,\n                    'http://schema.org/name',\n                    value.dctitle,\n                    detect_language(value.dctitle)));\n                }\n              }\n              break;\n\n            case 'foafmaker':\n              var makers = [];\n              if (Array.isArray(value)) {\n                makers = value;\n              } else {\n                makers.push(value);\n              }\n\n              for (var n in makers) {\n                var maker = makers[n];\n\n                var author_id = maker.foafperson.rdfabout;\n\n                triples.push(triple(subject_id,\n                  'http://schema.org/author',\n                  author_id));\n\n                // type, to do: need to handle organisations as authors\n                triples.push(triple(author_id,\n                  'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n                  'http://schema.org/Person'));\n\n                triples.push(triple(author_id,\n                  'http://schema.org/identifier',\n                  author_id));\n\n                if (Array.isArray(maker.foafperson.foafname)) {\n\n                  for (var fn in maker.foafperson.foafname) {\n                    if (!have_value_already('foafname', maker.foafperson.foafname[fn])) {\n                      triples.push(triple(author_id,\n                        'http://schema.org/name',\n                        maker.foafperson.foafname[fn], detect_language(maker.foafperson.foafname[fn])));\n\n                    }\n                  }\n                } else {\n                  triples.push(triple(author_id,\n                    'http://schema.org/name',\n                    maker.foafperson.foafname, detect_language(maker.foafperson.foafname)));\n\n                }\n              }\n              break;\n\n\n            default:\n              break;\n          }\n        } else {\n          var value = decodeHTMLEntities(json.rdfrdf.rdfdescription[s][k]);\n\n          switch (k) {\n\n            case 'dctitle':\n              if (!have_value_already('title', value)) {\n                triples.push(triple(subject_id,\n                  'http://schema.org/name',\n                  value,\n                  detect_language(value)));\n              }\n              break;\n\n            case 'dcdescription':\n              if (!have_value_already('description', value)) {\n                triples.push(triple(subject_id,\n                  'http://schema.org/description',\n                  value,\n                  detect_language(value)));\n              }\n              break;\n\n            case 'dcpublisher':\n              triples.push(triple(subject_id,\n                'http://schema.org/publisher',\n                value,\n                detect_language(value)));\n              break;\n\n            case 'prismpublicationname':\n              if (!have_value_already('publicationName', value)) {\n                triples.push(triple(subject_id,\n                  'http://prismstandard.org/namespaces/basic/2.1/publicationName',\n                  value,\n                  detect_language(value)));\n              }\n              break;\n\n            case 'prismvolume':\n              triples.push(triple(subject_id,\n                'http://schema.org/volumeNumber',\n                value));\n              break;\n            case 'prismnumber':\n              triples.push(triple(subject_id,\n                'http://schema.org/issueNumber',\n                value));\n              break;\n            case 'prismstartingpage':\n              triples.push(triple(subject_id,\n                'http://schema.org/pageStart',\n                value));\n              break;\n            case 'prismendingpage':\n              triples.push(triple(subject_id,\n                'http://schema.org/pageEnd',\n                value));\n              break;\n\n            case 'prismpublicationdate':\n              triples.push(triple(subject_id,\n                'http://schema.org/datePublished',\n                value));\n              break;\n\n            case 'prismdoi':\n              triples.push(triple(subject_id,\n                'http://schema.org/identifier',\n                'http://identifiers.org/doi/' + value));\n\n              triples.push(triple(subject_id,\n                'http://prismstandard.org/namespaces/basic/2.1/doi',\n                value));\n              break;\n\n            case 'ciniinaid':\n              triples.push(triple(subject_id,\n                'http://schema.org/url',\n                'http://ci.nii.ac.jp/naid/' + value));\n              break;\n\n            default:\n              break;\n          }\n        }\n      }\n    }\n  }\n\n  // do stuff\t\n  output(doc, triples);\n}\n\n\nfunction(doc) {\n  if (doc._id.match(/ci.nii.ac.jp\\/naid/)) {\n    message(doc);\n  }\n}"
       },
       "modified": {
           "map": "function(doc) {\n  if (doc._id.match(/ci.nii.ac.jp\\/naid/)) {\n     if (doc.message) {\n      emit(doc['message-modified'], doc._id);\n    }\n  }\n}"
       }
   }
}