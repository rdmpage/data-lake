# IPNI queries

## IPNI query to get refs for names and basionyms

Idea is can we generate a complete bibliogrpahy for the species in a genus,
including basionyms, etc.

SELECT *
WHERE
{
	?name <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> "Cypringlea" . 
    ?name <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ?nameComplete . 
    ?name <http://rs.tdwg.org/ontology/voc/Common#publishedIn> ?publishedIn .
    OPTIONAL {
        ?name <http://rs.tdwg.org/ontology/voc/TaxonName#hasBasionym> ?basionym .
        ?basionym <http://rs.tdwg.org/ontology/voc/Common#publishedIn> ?basionymPublishedIn .
    }
}

## extend to get actual papers

SELECT *
WHERE
{
	?name <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> "Cypringlea" . 
    ?name <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ?nameComplete . 
    ?name <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?publishedInCitation .
    ?publishedInCitation <http://schema.org/name> ?title .
    OPTIONAL {
        ?name <http://rs.tdwg.org/ontology/voc/TaxonName#hasBasionym> ?basionym .
        ?basionym <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?basionymPublishedInCitation .
        ?basionymPublishedInCitation <http://schema.org/name> ?basionymTitle .
    }
    
   
}