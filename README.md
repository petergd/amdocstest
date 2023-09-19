# amdocstest

## Usage

***Get Tags***

    http://127.0.0.1:8000/api/tags
	
***Get All Products***

    http://127.0.0.1:8000/api/products
	
***Get Products by Category***

You can search using category name, e.g. clothing

    http://127.0.0.1:8000/api/products/category/clothing
	
***Add New Product***

You can quickly add a product by posting JSON having the following attributes

 - **name** (string). Set the name of the product. 
 - **code** (string). In this example uuid is used.
 - **category** (string). Either an existing category name or a new one.
 - **price** (float). The product price.
 - **release_date** (string). Release date for the product, in format YYYY-MM-DD.
 - **tags** (string). Either existing tags or new ones, as comma-separated values e.g. "tag1,tag2,tag3"

***Modify Product***

You can modify a product by posting JSON having the following attributes

 - **pid** (integer). Required attribute is the product id. 
 - **name** (string). The name of the product or empty string "" if to be left as is. 
 - **code** (string). Product code (uuid in this example) or empty string "" if to be left as is.
 - **category** (string). Either a new existing category name or empty string "" if to be left as is.
 - **price** (float). The new product price or null.
 - **release_date** (string). New release date for the product, in format YYYY-MM-DD or null.
 - **tags** (string). Either new existing tags, as comma-separated values e.g. "tag1,tag2,tag3" or empty string "".
 
 


