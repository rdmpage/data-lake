# Annotations

Taxonomic name databases such as ION and IPNI often contain “microreferences” to the page in an article where a name first appears. Most web identifiers refer to documents (e.g., whole web pages, PDFs, XML files, etc.) rather than part of those documents. How can we link to parts of a digital document? In some cases documents parts may themselves be available as documents, for example, individual scanned pages in BHL each have a URL based on the PageID of that page. Hence we could refer to the page on which a new species name appears using the BHL PageID. For other documents an obvious approach is to use [fragment identifiers](https://en.wikipedia.org/wiki/Fragment_identifier) and https://www.w3.org/TR/annotation-model/#selectors 

## HTML

Use id of element containing the things being annotated.

## XML

We can use XPointer to identify locations in a XML document.  For example, #xpointer(//Rube)

```
{
  "@context": "http://www.w3.org/ns/anno.jsonld",
  "id": "http://example.org/anno22",
  "type": "Annotation",
  "body": "http://example.org/note1",
  "target": {
    "source": "http://example.org/page1.html",
    "selector": {
      "type": "XPathSelector",
      "value": "/html/body/p[2]/table/tr[2]/td[3]/span"
    }
  }
}
```

## PDF
 For a PDF we can use the **page** fragment identifier (see 
[RFC 3778 - The application/pdf Media Type](https://tools.ietf.org/html/rfc3778)).

#page=<pagenum>

where **pagenum** is the ordinal page number starting at 1. Web browsers such as Chrome, and browser plugins such as Adobe Acrobat (https://helpx.adobe.com/acrobat/kb/link-html-pdf-page-acrobat.html) support this.

"selector": {
  "type": "FragmentSelector",
  "value": "page=10",
  "conformsTo": "http://tools.ietf.org/rfc/rfc3778"
}

### Annotation model

{
  "@context": "http://www.w3.org/ns/anno.jsonld",
  "id": "http://example.org/anno20",
  "type": "Annotation",
  "body": {

***
    “@type” : “TextualBody”,
    “purpose": “tagging”, (tagging names)
    “purpose": “linking”, (where name is linked to a URI)


    “value”: <scientific name>,
    "rdfs:seeAlso": { “@id”: <uri for name> },
 ***   

***
    “@id”: <uri for name>
***

  },
  "target": {
     “source” : <uri for document/page e.g. page image, XML document, PDF>,
     “scope” : <uri for thing, e.g. DOI for article, BHL page id)
     "selector": {
       "type": "FragmentSelector",
       "value": "page=<page number>”,
       "conformsTo": "http://tools.ietf.org/rfc/rfc3778"
     },
     {
       "type": "XPathSelector",
       "value": "/html/body/p[2]/table/tr[2]/td[3]/span"
     },
    {
      "type": "TextPositionSelector",
      "start": 412,
      "end": 795
    },
    {
      "type": "TextQuoteSelector",
      "exact": "anotation",
      "prefix": "this is an ",
      "suffix": " that has some"
    }
  }
}



{
      "@id": "http://www.biodiversitylibrary.org/page/15775524#name_1/body",
      "@type": "oa:TextualBody",
      "rdf:value": "Formosa",
      "rdfs:seeAlso": {
        "@id": "http://eol.org/pages/97210"
      },
      "oa:hasPurpose": {
        "@id": "oa:tagging"
      }
    },






## Fragment identifiers and annotations

It is tempting to simply link to a fragment identifier, however I think we can gain more if we treat these links to document parts as annotations. This would enable us to model the outputs of three distinct processes in the same way, namely:

- taxonomic databases telling us which page a name occurs on
- automated name finding tools telling us that a page contains a scientific name
- manual annotation using tools such as hyptothes.is to highlight a name in a block of text


We can then view each process as generating annotations, each with a well-defined link to the annotation.



## References

- “RFC 3778 - The application/pdf Media Type". The Internet Society. May 2004. Retrieved Aug 26, 2013.

