# Wikidata queries


## Wikidata find author based on IPNI id 

Wikidata find author based on IPNI id 

```
SELECT *
WHERE
{
	  ?item wdt:P586 "37150-1" .
	  ?item rdfs:label ?label .
	  FILTER(langMatches(lang(?label), "EN"))}
}
```

Find  author and other identifiers and image based on IPNI id

```
SELECT *
WHERE
{
	  ?item wdt:P586 "37150-1" .
	  ?item rdfs:label ?label .
	OPTIONAL {
	   ?item wdt:P213 ?isni .
		}
	  OPTIONAL {
	   ?item wdt:P214 ?viaf .
		}    
	  OPTIONAL {
	   ?item wdt:P18 ?image .
		} 
	  OPTIONAL {
	   ?item wdt:P496 ?orcid .
		} 		
	  OPTIONAL {
	   ?item wdt:P586 ?ipni .
		} 
	  OPTIONAL {
	   ?item wdt:P2006 ?zoobank .
		} 
	  FILTER(langMatches(lang(?label), "EN"))
}
```

Note that prefix "wdt" expands to http://www.wikidata.org/prop/direct/
