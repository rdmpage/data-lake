/*

Shared code


*/

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/25715455
function isObject (item) {
  return (typeof item === "object" && !Array.isArray(item) && item !== null);
}

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/21445415
function uniques(arr) {
  var a = [];
  for (var i = 0, l = arr.length; i < l; i++)
    if (a.indexOf(arr[i]) === -1 && arr[i] !== '')
      a.push(arr[i]);
  return a;
}

		
//----------------------------------------------------------------------------------------
// Store a triple with optional language code
function triple(subject, predicate, object, language) {
  var triple = [];
  triple[0] = subject;
  triple[1] = predicate;
  triple[2] = object;
  
  if (typeof language === 'undefined') {
  } else {
    triple[3] = language;
  }
  
  return triple;
}

//----------------------------------------------------------------------------------------
// Store a quad (not used at present)
function quad(subject, predicate, object, context) {
  var triple = [];
  triple[0] = $subject;
  triple[1] = $predicate;
  triple[2] = $object;
  triple[3] = $context;
  
  return triple;
}

//----------------------------------------------------------------------------------------
// Enclose triple in suitable wrapping for HTML display or triplet output
function wrap(s, html) {
if (s) {

	console.log(s);

  if (s.match(/^(http|urn|_:)/)) {
    s = s.replace(/\\_/g, '_');

    // handle < > in URIs such as SICI-based DOIs
    s = s.replace(/</g, '%3C');
    s = s.replace(/>/g, '%3E');
  
    if (html) {
      s = '&lt;' + s + '&gt;';
    } else {
      s = '<' + s + '>';
    }
  } else {
    s = '"' + s.replace(/"/g, '\\"') + '"';
  }}
  return s;
}

//----------------------------------------------------------------------------------------
// https://css-tricks.com/snippets/javascript/htmlentities-for-javascript/
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

//----------------------------------------------------------------------------------------
function output(doc, triples) {
  if (1) {
	  // Output triples
	  
	  var nquads = '';
	  
	  var html = '<table width="100%">';
	  for (var i in triples) {
		var s = 0;
		var p = 1;
		var o = 2;
		var lang = 3;
		// SPO
		//console.log(JSON.stringify(triples[i]));
	
	
	
		//html += '<tr><td>' + wrap(triples[i][s], true) + '</td><td>' + wrap(triples[i][p], true) + '</td><td>' + wrap(triples[i][o], true) + ' .</td></tr>';
	
	    nquads += wrap(triples[i][s], false) 
	    	+ ' ' + wrap(triples[i][p], false) 
	    	+ ' ' + wrap(triples[i][o], false);
	    	
	    if (triples[i][lang]) {
	    	nquads += '@' + triples[i][lang];
	    }
	    	
	    nquads += ' .' + "\n";
	
	
	  }
	  html += '</table>';
	  
	  html += '<pre>' + htmlEntities(nquads) + '</pre>';
	  
	  $('#output').html(html);
	  
	  
	  // graph
	  if (1)
	  {
		  var dot = 'digraph G { rankdir = LR;';
	  
		  var nodes = [];
	  
	  
		  for (var i in triples) {
			var s = 0;
			var p = 1;
			var o = 2;
			var lang = 3;
		
			var subject = triples[i][s];
			if (nodes.indexOf(subject) == -1) {
				nodes.push(subject);
			}
			var index = nodes.indexOf(subject);
		
			if (index > 0) {
				index = subject;
			}
		
		
			var predicate = triples[i][p];
			var lastBit = predicate.substring(predicate.lastIndexOf("/")+1);
			lastBit = lastBit.substring(lastBit.lastIndexOf("#")+1);
		
			var object = triples[i][o];
			if (object.length > 50) {
			   if (object.match(/^http/)) {
			   } else {
				object = object.substring(0, 50) + '...';
			   }
			}		
		
			//dot += '"' + triples[i][s] + '" -- "' + triples[i][o] + '" [label="' + lastBit + '"];' + "\n";
			dot += '"' + index + '" -> "' + object + '" [label="' + lastBit + '"];' + "\n";
		 }
		 dot += '}';
		 
		 //alert(dot);
	  
		   var graph = Viz(dot, "svg", "dot");
		  $('#graph').html(graph);
		}	  
	  
	   // convert RDF to JSON-LD
jsonld.fromRDF(nquads, {format: 'application/nquads'}, function(err, j) {

//  $('#jsonld').html(JSON.stringify(j, null, 2));

// make nice
 var context = {
  "@vocab" : "http://schema.org/",
  
  // RDF syntax
  "rdf" : "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
   "type": "rdf:type",
   
  "rdf" : "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
   "type": "rdf:type",

"rdfs" : "http://www.w3.org/2000/01/rdf-schema#",  
  
  
  
  // Dublin Core
  "dc" : "http://purl.org/dc/terms/",
  
  
//  "identifier" :"http://purl.org/dc/terms/identifier",
//  "title" : "http://purl.org/dc/terms/title",

  // Bibio
 /* "volume" : "http://purl.org/ontology/bibo/volume",
  "issue" : "http://purl.org/ontology/bibo/issue",
  "pages" : "http://purl.org/ontology/bibo/pages",*/
  
  // Prism
  "publicationName" : "http://prismstandard.org/namespaces/basic/2.1/publicationName",
  "doi" : "http://prismstandard.org/namespaces/basic/2.1/doi",
  
  // Open Annotation
  "oa" : "http://www.w3.org/ns/oa#",  
  
  // Darwin Core
 "dwc": "http://rs.tdwg.org/dwc/terms/",  
 
 // LOCN
 "locn":"http://www.w3.org/ns/locn#",
 
 // id.loc.gov
 "sha1":"http://id.loc.gov/vocabulary/preservation/cryptographicHashFunctions/sha1",
 
 // tdwg
 "tc":"http://rs.tdwg.org/ontology/voc/Common#",
 "tn":"http://rs.tdwg.org/ontology/voc/TaxonName#",
 "tpub":"http://rs.tdwg.org/ontology/voc/PublicationCitation#",
  
  // Identifiers
  "DOI" : "http://identifiers.org/doi/",
  "HANDLE":"http://hdl.handle.net/",
  "ISBN": "http://identifiers.org/isbn/",  
  "ISSN": "http://identifiers.org/issn/",
  "ORCID": "http://orcid.org/",
  "PMID" : "http://identifiers.org/pmid/",
  "PMC" : "http://identifiers.org/pmc/"
  
};

jsonld.compact(j, context, function(err, compacted) {
  $('#jsonld').html('<pre>' + JSON.stringify(compacted, null, 2) + '</pre>');
  });
  
  
}); 
  
  
  } else {
      // CouchDB
	  for (var i in triples) {
		var s = 0;
		var p = 1;
		var o = 2;
		//emit([wrap(triples[i][s], false), wrap(triples[i][p], false), wrap(triples[i][o], false)], 1);
		
    var lang = 3;

      var nquads = wrap(triples[i][s], false) 
	    	+ ' ' + wrap(triples[i][p], false) 
	    	+ ' ' + wrap(triples[i][o], false);
	    if (triples[i][lang]) {
	    	nquads += '@' + triples[i][lang];
	    }
	    	
	    nquads += ' .' + "\n";


      //emit(doc._id, nquads);		
		
	  }
    
    
  }
}
