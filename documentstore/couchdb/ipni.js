{
   "_id": "_design/ipni",
   "_rev": "7-27b843ee2cc88801b425aed2f9e1c972",
   "language": "javascript",
   "lists": {
       "n-triples": "function(head,req) { var headers = ''; var row; start({ 'headers': { 'Content-Type': 'text/plain' } }); while(row = getRow()) { send(row.value); } }"
   },
   "views": {
       "nt": {
           "map": "/*\n\nShared code\n\n\n*/\n//----------------------------------------------------------------------------------------\n// Store a triple with optional language code\nfunction triple(subject, predicate, object, language) {\n  var triple = [];\n  triple[0] = subject;\n  triple[1] = predicate;\n  triple[2] = object;\n\n  if (typeof language === 'undefined') {} else {\n    triple[3] = language;\n  }\n\n  return triple;\n}\n\n//----------------------------------------------------------------------------------------\n// Store a quad (not used at present)\nfunction quad(subject, predicate, object, context) {\n  var triple = [];\n  triple[0] = $subject;\n  triple[1] = $predicate;\n  triple[2] = $object;\n  triple[3] = $context;\n\n  return triple;\n}\n\n//----------------------------------------------------------------------------------------\n// Enclose triple in suitable wrapping for HTML display or triplet output\nfunction wrap(s, html) {\n  if (s.match(/^(http|urn|_:)/)) {\n    s = s.replace(/\\\\_/g, '_');\n\n    // handle < > in URIs such as SICI-based DOIs\n    s = s.replace(/</g, '%3C');\n    s = s.replace(/>/g, '%3E');\n\n   if (html) {\n      s = '&lt;' + s + '&gt;';\n    } else {\n      s = '<' + s + '>';\n    }\n  } else {\n    s = '\"' + s.replace(/\"/g, '\\\\\"') + '\"';\n  }\n  return s;\n}\n\n//----------------------------------------------------------------------------------------\n// https://css-tricks.com/snippets/javascript/htmlentities-for-javascript/\nfunction htmlEntities(str) {\n  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;');\n}\n\n//----------------------------------------------------------------------------------------\nfunction output(doc, triples) {\n    // CouchDB\n    for (var i in triples) {\n      var s = 0;\n      var p = 1;\n      var o = 2;\n\n      var lang = 3;\n\n      var nquads = wrap(triples[i][s], false) \n\t    \t+ ' ' + wrap(triples[i][p], false) \n\t    \t+ ' ' + wrap(triples[i][o], false);\n\t    if (triples[i][lang]) {\n\t    \tnquads += '@' + triples[i][lang];\n\t    }\n\t    \t\n\t    nquads += ' .' + \"\\n\";\n\n\n      emit(doc._id, nquads);\n    }\n}\n\n/*\nxml2json v 1.1\ncopyright 2005-2007 Thomas Frank\n\nThis program is free software under the terms of the \nGNU General Public License version 2 as published by the Free \nSoftware Foundation. It is distributed without any warranty.\n*/\n\nxml2json={\n\tparser:function(xmlcode,ignoretags,debug){\n\t\tif(!ignoretags){ignoretags=\"\"};\n\t\txmlcode=xmlcode.replace(/\\s*\\/>/g,'/>');\n\t\txmlcode=xmlcode.replace(/<\\?[^>]*>/g,\"\").replace(/<\\![^>]*>/g,\"\");\n\t\tif (!ignoretags.sort){ignoretags=ignoretags.split(\",\")};\n\t\tvar x=this.no_fast_endings(xmlcode);\n\t\tx=this.attris_to_tags(x);\n\t\tx=escape(x);\n\t\tx=x.split(\"%3C\").join(\"<\").split(\"%3E\").join(\">\").split(\"%3D\").join(\"=\").split(\"%22\").join(\"\\\"\");\n\t\tfor (var i=0;i<ignoretags.length;i++){\n\t\t\tx=x.replace(new RegExp(\"<\"+ignoretags[i]+\">\",\"g\"),\"*$**\"+ignoretags[i]+\"**$*\");\n\t\t\tx=x.replace(new RegExp(\"</\"+ignoretags[i]+\">\",\"g\"),\"*$***\"+ignoretags[i]+\"**$*\")\n\t\t};\n\t\tx='<JSONTAGWRAPPER>'+x+'</JSONTAGWRAPPER>';\n\t\tthis.xmlobject={};\n\t\tvar y=this.xml_to_object(x).jsontagwrapper;\n\t\tif(debug){y=this.show_json_structure(y,debug)};\n\t\treturn y\n\t},\n\txml_to_object:function(xmlcode){\n\t\tvar x=xmlcode.replace(/<\\//g,\"�\");\n\t\tx=x.split(\"<\");\n\t\tvar y=[];\n\t\tvar level=0;\n\t\tvar opentags=[];\n\t\tfor (var i=1;i<x.length;i++){\n\t\t\tvar tagname=x[i].split(\">\")[0];\n\t\t\t\n\t\t\t//tagname = tagname.replace(/(:|%3A)/i, '_colon_');\n\t\t\t//console.log(tagname);\n\t\t\t\n\t\t\topentags.push(tagname);\n\t\t\tlevel++\n\t\t\ty.push(level+\"<\"+x[i].split(\"�\")[0]);\n\t\t\twhile(x[i].indexOf(\"�\"+opentags[opentags.length-1]+\">\")>=0){level--;opentags.pop()}\n\t\t};\n\t\tvar oldniva=-1;\n\t\tvar objname=\"this.xmlobject\";\n\t\tfor (var i=0;i<y.length;i++){\n\t\t\tvar preeval=\"\";\n\t\t\tvar niva=y[i].split(\"<\")[0];\n\t\t\tvar tagnamn=y[i].split(\"<\")[1].split(\">\")[0];\n\t\t\ttagnamn=tagnamn.toLowerCase();\n\t\t\t\n\t\t\t//tagnamn = tagnamn.replace(/(:|%3A)/i, '_colon_');\n\t\t\t\n\t\t\tvar rest=y[i].split(\">\")[1];\n\t\t\tif(niva<=oldniva){\n\t\t\t\tvar tabort=oldniva-niva+1;\n\t\t\t\tfor (var j=0;j<tabort;j++){objname=objname.substring(0,objname.lastIndexOf(\".\"))}\n\t\t\t};\n\t\t\tobjname+=\".\"+tagnamn;\n\t\t\tvar pobject=objname.substring(0,objname.lastIndexOf(\".\"));\n\t\t\tif (eval(\"typeof \"+pobject) != \"object\"){preeval+=pobject+\"={value:\"+pobject+\"};\\n\"};\n\t\t\tvar objlast=objname.substring(objname.lastIndexOf(\".\")+1);\n\t\t\tvar already=false;\n\t\t\tfor (k in eval(pobject)){if(k==objlast){already=true}};\n\t\t\tvar onlywhites=true;\n\t\t\tfor(var s=0;s<rest.length;s+=3){\n\t\t\t\tif(rest.charAt(s)!=\"%\"){onlywhites=false}\n\t\t\t};\n\t\t\tif (rest!=\"\" && !onlywhites){\n\t\t\t\tif(rest/1!=rest){\n\t\t\t\t\trest=\"'\"+rest.replace(/\\'/g,\"\\\\'\")+\"'\";\n\t\t\t\t\trest=rest.replace(/\\*\\$\\*\\*\\*/g,\"</\");\n\t\t\t\t\trest=rest.replace(/\\*\\$\\*\\*/g,\"<\");\n\t\t\t\t\trest=rest.replace(/\\*\\*\\$\\*/g,\">\")\n\t\t\t\t}\n\t\t\t} \n\t\t\telse {rest=\"{}\"};\n\t\t\tif(rest.charAt(0)==\"'\"){rest='unescape('+rest+')'};\n\t\t\tif (already && !eval(objname+\".sort\")){preeval+=objname+\"=[\"+objname+\"];\\n\"};\n\t\t\tvar before=\"=\";after=\"\";\n\t\t\tif (already){before=\".push(\";after=\")\"};\n\t\t\tvar toeval=preeval+objname+before+rest+after;\n\t\t\t\n\t\t\t//console.log('toeval='+toeval);\n\t\t\t\n\t\t\teval(toeval);\n\t\t\tif(eval(objname+\".sort\")){objname+=\"[\"+eval(objname+\".length-1\")+\"]\"};\n\t\t\toldniva=niva\n\t\t};\n\t\treturn this.xmlobject\n\t},\n\tshow_json_structure:function(obj,debug,l){\n\t\tvar x='';\n\t\tif (obj.sort){x+=\"[\\n\"} else {x+=\"{\\n\"};\n\t\tfor (var i in obj){\n\t\t\tif (!obj.sort){x+=i+\":\"};\n\t\t\tif (typeof obj[i] == \"object\"){\n\t\t\t\tx+=this.show_json_structure(obj[i],false,1)\n\t\t\t}\n\t\t\telse {\n\t\t\t\tif(typeof obj[i]==\"function\"){\n\t\t\t\t\tvar v=obj[i]+\"\";\n\t\t\t\t\t//v=v.replace(/\\t/g,\"\");\n\t\t\t\t\tx+=v\n\t\t\t\t}\n\t\t\t\telse if(typeof obj[i]!=\"string\"){x+=obj[i]+\",\\n\"}\n\t\t\t\telse {x+=\"'\"+obj[i].replace(/\\'/g,\"\\\\'\").replace(/\\n/g,\"\\\\n\").replace(/\\t/g,\"\\\\t\").replace(/\\r/g,\"\\\\r\")+\"',\\n\"}\n\t\t\t}\n\t\t};\n\t\tif (obj.sort){x+=\"],\\n\"} else {x+=\"},\\n\"};\n\t\tif (!l){\n\t\t\tx=x.substring(0,x.lastIndexOf(\",\"));\n\t\t\tx=x.replace(new RegExp(\",\\n}\",\"g\"),\"\\n}\");\n\t\t\tx=x.replace(new RegExp(\",\\n]\",\"g\"),\"\\n]\");\n\t\t\tvar y=x.split(\"\\n\");x=\"\";\n\t\t\tvar lvl=0;\n\t\t\tfor (var i=0;i<y.length;i++){\n\t\t\t\tif(y[i].indexOf(\"}\")>=0 || y[i].indexOf(\"]\")>=0){lvl--};\n\t\t\t\ttabs=\"\";for(var j=0;j<lvl;j++){tabs+=\"\\t\"};\n\t\t\t\tx+=tabs+y[i]+\"\\n\";\n\t\t\t\tif(y[i].indexOf(\"{\")>=0 || y[i].indexOf(\"[\")>=0){lvl++}\n\t\t\t};\n\t\t\tif(debug==\"html\"){\n\t\t\t\tx=x.replace(/</g,\"&lt;\").replace(/>/g,\"&gt;\");\n\t\t\t\tx=x.replace(/\\n/g,\"<BR>\").replace(/\\t/g,\"&nbsp;&nbsp;&nbsp;&nbsp;\")\n\t\t\t};\n\t\t\tif (debug==\"compact\"){x=x.replace(/\\n/g,\"\").replace(/\\t/g,\"\")}\n\t\t};\n\t\treturn x\n\t},\n\tno_fast_endings:function(x){\n\t\tx=x.split(\"/>\");\n\t\tfor (var i=1;i<x.length;i++){\n\t\t\tvar t=x[i-1].substring(x[i-1].lastIndexOf(\"<\")+1).split(\" \")[0];\n\t\t\tx[i]=\"></\"+t+\">\"+x[i]\n\t\t}\t;\n\t\tx=x.join(\"\");\n\t\treturn x\n\t},\n\tattris_to_tags: function(x){\n\t\tvar d=' =\"\\''.split(\"\");\n\t\tx=x.split(\">\");\n\t\tfor (var i=0;i<x.length;i++){\n\t\t\tvar temp=x[i].split(\"<\");\n\t\t\tfor (var r=0;r<4;r++){temp[0]=temp[0].replace(new RegExp(d[r],\"g\"),\"_jsonconvtemp\"+r+\"_\")};\n\t\t\tif(temp[1]){\n\t\t\t\ttemp[1]=temp[1].replace(/'/g,'\"');\n\t\t\t\ttemp[1]=temp[1].split('\"');\n\t\t\t\tfor (var j=1;j<temp[1].length;j+=2){\n\t\t\t\t\tfor (var r=0;r<4;r++){temp[1][j]=temp[1][j].replace(new RegExp(d[r],\"g\"),\"_jsonconvtemp\"+r+\"_\")}\n\t\t\t\t};\n\t\t\t\ttemp[1]=temp[1].join('\"')\n\t\t\t};\n\t\t\tx[i]=temp.join(\"<\")\n\t\t};\n\t\tx=x.join(\">\");\n\t\tx=x.replace(/ ([^=]*)=([^ |>]*)/g,\"><$1>$2</$1\");\n\t\tx=x.replace(/>\"/g,\">\").replace(/\"</g,\"<\");\n\t\tfor (var r=0;r<4;r++){x=x.replace(new RegExp(\"_jsonconvtemp\"+r+\"_\",\"g\"),d[r])}\t;\n\t\treturn x\n\t}\n};\n\n\nif(!Array.prototype.push){\n\tArray.prototype.push=function(x){\n\t\tthis[this.length]=x;\n\t\treturn true\n\t}\n};\n\nif (!Array.prototype.pop){\n\tArray.prototype.pop=function(){\n  \t\tvar response = this[this.length-1];\n  \t\tthis.length--;\n  \t\treturn response\n\t}\n};\n\n\n// http://stackoverflow.com/a/17076120\nfunction decodeHTMLEntities(text) {\n   var entities = [\n        ['amp', '&'],\n        ['apos', '\\''],\n        ['#x27', '\\''],\n        ['#x2F', '/'],\n        ['#39', '\\''],\n\t['#039', '\\''],\n        ['#47', '/'],\n        ['lt', '<'],\n        ['gt', '>'],\n        ['nbsp', ' '],\n        ['quot', '\"']\n    ];\n\n    text = String(text);\n\n    for (var i = 0, max = entities.length; i < max; ++i) {\n        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);\n    }\n\n    return text;\n}\n\nfunction message(doc) {\n\n\n  var subject_id = doc._id;\n  var triples = [];\n  var type = '';\n\n  var xml = doc.message.xml;\n\n  // administrivia\n  xml = xml.replace(/<\\?xml version=\"1.0\" encoding=\"UTF-8\"\\?>\\s*/i, '');\n  xml = xml.replace(/<\\?xml-stylesheet type=\"text\\/xsl\" href=\"lsid.rdf.xsl\"\\?>\\s*/, '');\n\n  // namespaces\n  xml = xml.replace(/<rdf:RDF\\s+(xmlns:\\w+=([a-zA-Z0-9:\\/\"\\.#-]+)\"\\s*)+?>/, '<rdf:RDF>');\n\n  // can't use colons\n  xml = xml.replace(/<(\\w+):(\\w+)/gm, '<$1$2');\n  xml = xml.replace(/<\\/(\\w+):(\\w+)>/gm, '</$1$2>');\n\n  // attributes that we missed\n  xml = xml.replace(/rdf:/gm, 'rdf');\n  xml = xml.replace(/tm:/gm, 'tm');\n\n  // remove linebreaks within attrribute lists - FFS\n  xml = xml.replace(/\"\\s*\\n/gm, '\" ');\n\n  var json = xml2json.parser(xml);\n\n  if (json.rdfrdf) {\n\n    triples.push(triple(subject_id,\n      'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n      'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName'));\n\n    triples.push(triple(subject_id,\n      'http://schema.org/identifier',\n      subject_id));\n\n    triples.push(triple(subject_id,\n      'http://schema.org/url',\n      'http://www.ipni.org/ipni/idPlantNameSearch.do?id=' + subject_id.replace(/urn:lsid:ipni.org:names:/, '')));\n\n\n    for (var k in json.rdfrdf.tntaxonname) {\n\n      if (typeof json.rdfrdf.tntaxonname[k] === 'object') {\n        var value = json.rdfrdf.tntaxonname[k];\n\n        switch (k) {\n          //------ types \n\n          case 'tntypifiedby':\n            var type_count = 1;\n\n            for (var m in value) {\n              var type_id = subject_id + '#type_' + type_count++;\n\n              triples.push(triple(subject_id,\n                'http://rs.tdwg.org/ontology/voc/TaxonName#typifiedBy',\n                type_id));\n\n\n              triples.push(triple(type_id,\n                'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n                'http://rs.tdwg.org/ontology/voc/TaxonName#NomenclaturalType'));\n\n              var nomenclaturaltype = {};\n\n              // do we have one or multiple types\n              if (value[m].tnnomenclaturaltype) {\n                nomenclaturaltype = value[m].tnnomenclaturaltype;\n              } else {\n                nomenclaturaltype = value[m];\n              }\n\n              for (var type_key in nomenclaturaltype) {\n                switch (type_key) {\n\n                  case 'dctitle':\n                    triples.push(triple(type_id,\n                      'http://purl.org/dc/terms/title',\n                      decodeHTMLEntities(nomenclaturaltype[type_key])));\n\n                    triples.push(triple(type_id,\n                      'http://schema.org/name',\n                      decodeHTMLEntities(nomenclaturaltype[type_key])));\n                    break;\n\n                  case 'tntypespecimen':\n                    triples.push(triple(type_id,\n                      'http://rs.tdwg.org/ontology/voc/TaxonName#typeSpecimen',\n                      decodeHTMLEntities(nomenclaturaltype[type_key])));\n                    break;\n\n                  case 'tntypeoftype':\n                    triples.push(triple(type_id,\n                      'http://rs.tdwg.org/ontology/voc/TaxonName#typeOfType',\n                      nomenclaturaltype[type_key].rdfresource));\n                    break;\n\n                  default:\n                    break;\n                }\n              }\n            }\n            break;\n\n            // author team\n            /*\n            <tn:authorteam>\n            <tm:Team>\n            <tm:name>Szlach. &amp; Kolan.</tm:name>\n            <tm:hasMember rdf:resource=\"urn:lsid:ipni.org:authors:35457-1\"\n            tm:index=\"1\"\n            tm:role=\"Publishing Author\"/>\n            <tm:hasMember rdf:resource=\"urn:lsid:ipni.org:authors:20019471-1\"\n            tm:index=\"2\"\n            tm:role=\"Publishing Author\"/>\n            </tm:Team>\n            </tn:authorteam>\n\n            */\n\n          case 'tnauthorteam':\n            var team_id = subject_id + '#team';\n\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#authorteam',\n              team_id));\n\n            triples.push(triple(team_id,\n              'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n              'http://rs.tdwg.org/ontology/voc/Team#Team'));\n\n            for (var m in value.tmteam) {\n              switch (m) {\n                case 'tmname':\n                  triples.push(triple(team_id,\n                    'http://schema.org/name',\n                    decodeHTMLEntities(value.tmteam[m])));\n\n                  triples.push(triple(team_id,\n                    'http://rs.tdwg.org/ontology/voc/Team#name',\n                    decodeHTMLEntities(value.tmteam[m])));\n\n                  break;\n\n                case 'tmhasmember':\n                  var members = [];\n                  if (Array.isArray(value.tmteam[m])) {\n                    members = value.tmteam[m];\n                  } else {\n                    members.push(value.tmteam[m]);\n                  }\n\n                  var role_count = 1;\n\n                  for (var n in members) {\n                    member = members[n];\n\n                    var author_id = member.rdfresource;\n                    var role_id = team_id + '/role_' + role_count++;\n                    \n                    triples.push(triple(team_id,\n                        'http://rs.tdwg.org/ontology/voc/Team#hasMember',\n                        role_id));\n                        \n\t\t\t\t\ttriples.push(triple(role_id,\n                        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',\n                        'http://schema.org/Role')); \n                        \n\t\t\t\t\ttriples.push(triple(role_id,\n                        'http://rs.tdwg.org/ontology/voc/Team#hasMember',\n                        author_id));    \n                        \n\t\t\t\t\ttriples.push(triple(role_id,\n                          'http://schema.org/position',\n                          String(member.tmindex)));                                                                   \n\n\t\t\t\t\ttriples.push(triple(role_id,\n                          'http://schema.org/roleName',\n                          String(member.tmrole)));                                                                   \n\n\n                  }\n                  break;\n\n                default:\n                  break;\n              }\n\n\n            }\n\n            break;\n\n          case 'tnnomenclaturalcode':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#nomenclaturalCode',\n              value.rdfresource));\n            break;\n\n \t  case 'tnhasbasionym':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#hasBasionym',\n              value.rdfresource));            \n\n\n          default:\n            break;\n        }\n\n      } else {\n        var value = decodeHTMLEntities(json.rdfrdf.tntaxonname[k]);\n\n        switch (k) {\n\n          case 'dcidentifier':\n            triples.push(triple(subject_id,\n              'http://purl.org/dc/terms/identifier',\n              'urn:lsid:organismnames.com:name:' + value));\n\n            triples.push(triple(subject_id,\n              'http://schema.org/identifier',\n              'urn:lsid:organismnames.com:name:' + value));\n            break;\n\n          case 'dctitle':\n            triples.push(triple(subject_id,\n              'http://purl.org/dc/terms/title',\n              value));\n\n            triples.push(triple(subject_id,\n              'http://schema.org/name',\n              value));\n            break;\n\n          case 'tnrankstring':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#rankString',\n              value));\n            break;\n\n          case 'tnnamecomplete':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete',\n              value));\n            break;\n\n          case 'tngenuspart':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#genusPart',\n              value));\n            break;\n\n          case 'tninfragenericepithet':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet',\n              value));\n            break;\n\n          case 'tnspecificepithet':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet',\n              value));\n            break;\n\n          case 'tninfraspecificepithet':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet',\n              value));\n            break;\n\n          case 'tnauthorship':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#authorship',\n              value));\n            break;\n\n          case 'tnyear':\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/TaxonName#year',\n              value));\n            break;\n\n          case 'tcompublishedin':\n            value = value.replace(/\\s+$/, '');\n            triples.push(triple(subject_id,\n              'http://rs.tdwg.org/ontology/voc/Common#publishedIn',\n              value));\n            break;\n\n          default:\n            break;\n        }\n      }\n    }\n  }\n\n  // do stuff\t\n  output(doc, triples);\n}\n\n\nfunction(doc) {\n  if (doc._id.match(/urn:lsid:ipni.org:names:/)) {\n    message(doc);\n  }\n}"
       },
       "modified": {
           "map": "function(doc) {\n  if (doc._id.match(/urn:lsid:ipni.org:names:/)) {\n     if (doc.message) {\n      emit(doc['message-modified'], doc._id);\n    }\n  }\n}"
       }
   }
}