import pickle
import pandas as pd
import sys
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import CountVectorizer

product_dataset = pd.read_csv('C:/xampp/htdocs/recommander_system/products.csv')

product_dataset["tags"]=product_dataset["about"]+product_dataset["category_name"]

product_dataset=product_dataset.drop(columns=["about","category_name"])

cv = CountVectorizer(max_features=3000,stop_words='english')

vector = cv.fit_transform(product_dataset["tags"].values.astype('U')).toarray()

similarity = cosine_similarity(vector)

pickle.dump(product_dataset,open('product_dataset.pkl','wb'))
pickle.dump(similarity,open('similarity.pkl','wb'))
# Load the product dataset and similarity matrix
products = pickle.load(open("product_dataset.pkl", 'rb'))
similarity = pickle.load(open("similarity.pkl", 'rb'))

# Get the list of product names from the dataset
products_list = products['name'].values

def recommend_product(product_name):
    try:
        # Find the index of the input product
        index = products[products['name'] == product_name].index[0]
        
        # Calculate the similarity for the given product
        distance = sorted(list(enumerate(similarity[index])), key=lambda vector: vector[1], reverse=True)
        
        # Get the top 5 recommended products
        recommended_products = []
        for i in distance[1:6]:  # Avoid returning the same product (index 0)
            recommended_products.append(products.iloc[i[0]]['name'])
        
        return recommended_products

    except IndexError:
        return "Product not found in dataset."

# Get the product name from command-line arguments (or default to 'Phone')
if len(sys.argv) > 1:
    product_name = sys.argv[1]
else:
    product_name = "wrong product name"  # You can change this to any default product name.

recommended_products = recommend_product(product_name)

# Output the result
print(recommended_products)

