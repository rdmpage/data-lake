# Data lake

Processing data for knowledge graphs

## Basic steps

1. Enqueue data in CouchDB
2. Dequeue identifiers and harvest them using resolvers
3. Use CouchDB views to convert native format (typically JSON) to triples
4. Export triples to a triple store
4. Query in SPARQL


## Data lakes

The document store can be regarded as a [data lake](https://en.wikipedia.org/wiki/Data_lake), i.e. a repository of data in different formats, ideally the original source format.

## Ideas

We harvest data. Many sources will be native JSON, store these as documents in CouchDB with source URL as _id, use a CouchDB view to convert to triples. Create one view per data type (to keep Javascript code manageable). Use MIME-type to distinguish between different data types. Can also harvest native RDF if uses acceptable vocabulary. Triples would be good native format for “linking” data sets. Could also support JSON-LD, either in CouchDB, or convert to n-triples and import directly.

Triples are imported into triple store and queried.

Use “groups” as convenient way to restrict queries, e.g. Mendeley literature group, EOL taxon group. Treat as schema.org itemLists.

Recode multiple identifiers as schema:identifier, use indirection (“object that has identifier”) as way to query across multiple sources of data, rather than try and resolve “sameAs” links. 

b-nodes always encoded as full, if arbitrary, URIs, which means we can refer to them later when mapping to actual identifiers. For example, if we have [’s’, schema:identifier, ‘doi’] and [‘q’, schema:identifier, ‘doi’] then s==q and we can access both sets of triples by [‘x’, schema:identifier, ‘doi’] .

Treat data as messages, if message body is empty we need to fetch data, so we have a message queue which we continually poll and add missing data.

May have CouchDB views that are used to investigate/clean/merge data, but always have a view that lists modified date and lists all derived triples.


