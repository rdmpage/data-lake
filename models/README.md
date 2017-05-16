# Modelling data

Notes on modelling data.

See https://github.com/TLmaK0/gravizo for how to embed DOT files.

## CiNii 

![Alt text](https://g.gravizo.com/source/custom_mark10?https%3A%2F%2Fraw.githubusercontent.com%2Frdmpage%2Fdata-lake%2Fmaster%2Fmodels%2FREADME.md)
<details> 
<summary></summary>
custom_mark10
	digraph G 
	{
		rankdir = LR;
		"0" -> "http://schema.org/ScholarlyArticle" [label="type"];
		"0" -> "http://ci.nii.ac.jp/naid/110004661805#article" [label="identifier"];
		"0" -> "日本産魚類の十一新種" [label="name"];
		"0" -> "社団法人日本動物学会" [label="publisher"];
		"0" -> "動物学雑誌" [label="publicationName"];
		"0" -> "0044-5118" [label="issn"];
		"0" -> "29" [label="volumeNumber"];
		"0" -> "339" [label="issueNumber"];
		"0" -> "7" [label="pageStart"];
		"0" -> "12" [label="pageEnd"];
		"0" -> "1917-01-30" [label="datePublished"];
		"0" -> "http://ci.nii.ac.jp/naid/110004661805" [label="url"];
		"0" -> "http://ci.nii.ac.jp/ncid/AN00166645#entity" [label="isPartOf"];
		"http://ci.nii.ac.jp/ncid/AN00166645#entity" -> "http://ci.nii.ac.jp/ncid/AN00166645#entity" [label="identifier"];
		"http://ci.nii.ac.jp/ncid/AN00166645#entity" -> "http://schema.org/Periodical" [label="type"];
		"http://ci.nii.ac.jp/ncid/AN00166645#entity" -> "動物学雑誌" [label="name"];
		"http://ci.nii.ac.jp/ncid/AN00166645#entity" -> "http://www.worldcat.org/issn/0044-5118" [label="identifier"];
		"http://ci.nii.ac.jp/ncid/AN00166645#entity" -> "0044-5118" [label="issn"];
		"0" -> "The Zoological Society of Japan" [label="publisher"];
		"0" -> "Doubutsugaku zasshi" [label="publicationName"];
		"0" -> "http://ci.nii.ac.jp/images/nopreview.jpg" [label="thumbnailUrl"];
		"0" -> "http://ci.nii.ac.jp/nrid/9000005011074#me" [label="author"];
		"http://ci.nii.ac.jp/nrid/9000005011074#me" -> "http://schema.org/Person" [label="type"];
		"http://ci.nii.ac.jp/nrid/9000005011074#me" -> "http://ci.nii.ac.jp/nrid/9000005011074#me" [label="identifier"];
		"http://ci.nii.ac.jp/nrid/9000005011074#me" -> "田中 茂穂" [label="name"];
	}
custom_mark10
</details>
