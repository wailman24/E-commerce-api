import pickle
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.cluster import KMeans
from flask import Flask, request, jsonify
from kneed import KneeLocator
import requests
import time
import threading


app = Flask(__name__)

def trigger_export():
    url = "http://127.0.0.1:8000/api/export-recommendation-data"
    headers = {"X-Secret-Key": "export_data"}
    try:
        response = requests.get(url, headers=headers)
        print(f"Triggered at {time.ctime()}: {response.json()}")
    except Exception as e:
        print(f"Error: {e}")

def periodic_trigger():
    while True:
        trigger_export()
        time.sleep(60)

threading.Thread(target=periodic_trigger, daemon=True).start()

### ---------- CONTENT-BASED ---------- ###

# data preprocessing fase :
product_dataset = pd.read_csv('../storage/app/products.csv')
product_dataset["tags"]=product_dataset["about"]+product_dataset["category_name"]
product_dataset=product_dataset.drop(columns=["about","category_name"])

# learning the dataset to make predictions:
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


@app.route('/recommend_content/<int:product_id>', methods=['GET'])
def recommend_product(product_id):
    try:
        # Check if the product ID exists in the dataset
        if product_id not in products['id'].values:
            return jsonify({"error": "Product ID not found in dataset."}), 404

        # Get the index of the product in the DataFrame
        index = products[products['id'] == product_id].index[0]

        # Calculate similarity scores
        distance = sorted(list(enumerate(similarity[index])), key=lambda vector: vector[1], reverse=True)

        # Extract recommended product names or IDs (customize as needed)
        recommended_products = [
            {
                "id": int(products.iloc[i[0]]['id']),
                "name": products.iloc[i[0]]['name']
            }
            for i in distance[1:10]  # Skip the first one (it's the same product)
        ]

        return jsonify({
            "input_id": int(product_id),
            "recommendations": recommended_products
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

### ---------- COLLABORATIVE FILTERING ---------- ###

ratings = pd.read_csv('../storage/app/reviews.csv')

# Step 1: Filter products rated more than 5 times
product_counts = ratings['Product ID'].value_counts()
popular_products = product_counts[product_counts > 5].index
filtered_ratings = ratings[ratings['Product ID'].isin(popular_products)]

# Step 2: Filter users who rated more than 10 products
user_counts = filtered_ratings['User ID'].value_counts()
active_users = user_counts[user_counts > 10].index
filtered_ratings = filtered_ratings[filtered_ratings['User ID'].isin(active_users)]

#Step 3: transform the dataset into a metrics (pivote_table) 
df_colab_pivot = filtered_ratings.pivot_table(columns="Product ID", index="User ID", values="Rating").fillna(0)

#searching for optimal k number of clusters
# Step 1: Calculate SSE for a range of k values
sse = []
K_range = range(1, 21)
for k in K_range:
    kmeans = KMeans(n_clusters=k, n_init=5, init='k-means++', random_state=42)
    kmeans.fit(df_colab_pivot)
    sse.append(kmeans.inertia_)

# Step 2: Find elbow point
knee = KneeLocator(K_range, sse, curve="convex", direction="decreasing")
elbow_k = knee.elbow

# Step 3: Set a minimum k
best_k = max(elbow_k if elbow_k else 5, 5)

# Step 4: Cluster with the optimal k
kmeans = KMeans(n_clusters=best_k, n_init=5, init='k-means++', random_state=0)
y_kmeans = kmeans.fit_predict(df_colab_pivot)
df_colab_pivot['cluster'] = y_kmeans

@app.route('/recommend_users/<int:user_id>', methods=['GET'])
def recommend_products_for_users(user_id):
    try:
        if user_id not in df_colab_pivot.index:
            return jsonify({"error": f"User {user_id} not found."}), 404

        # Get the cluster of the given user
        cluster_n = df_colab_pivot.loc[user_id, 'cluster']

        # Get all users in the same cluster
        cluster_users = df_colab_pivot[df_colab_pivot['cluster'] == cluster_n]

        # Remove the cluster column
        cluster_users = cluster_users.drop(columns='cluster')

        # Get products the user has already rated
        rated_by_user = cluster_users.loc[user_id]
        already_rated = rated_by_user[rated_by_user > 0].index

        # Calculate average ratings per product in the cluster
        product_means = cluster_users.mean().sort_values(ascending=False)

        # Remove already rated products
        recommendations = product_means.drop(already_rated).head(9)

        # Create a list of recommended product info
        recommended_products = []

        for product_id in recommendations.index:
             product_info = filtered_ratings[filtered_ratings['Product ID'] == product_id]
             if not product_info.empty:
                recommended_products.append({
                    "id": product_id,
                    "name": product_info.iloc[0]['Product Name']
                })

        return jsonify({
            "input_id": int(user_id),
            "recommendations": recommended_products
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

### ---------- POPULARITY-BASED ---------- ###

# Step 1: Group by Product ID and aggregate average rating, count, and take one product name
popular = ratings.groupby('Product ID').agg({
    'Rating': ['mean', 'count'],
    'Product Name': 'first'  
})

# Step 2: Flatten the column names
popular.columns = ['average_rating', 'rating_counts', 'Product Name']

# Step 3: Sort by average rating and select top 10
popular = popular.sort_values('average_rating', ascending=False)

# Step 4: Reset index so Product ID becomes a column
popular = popular.reset_index()

# Step 5: Reorder columns
popular = popular[['Product ID', 'Product Name', 'rating_counts', 'average_rating']]

# Step 6: filter the dataset to take only the products highly rated at least 100 times 
ratings_mean_count = popular[(popular['average_rating'] > 3) & (popular['rating_counts'] > 100)]
ratings_mean_count = ratings_mean_count.sort_values(by=['rating_counts'], ascending=[ False]).head(15)


@app.route('/recommend_popular', methods=['GET'])
def popular_products():

    top_products = ratings_mean_count.sort_values('average_rating', ascending=False).head(15)

    # Create a list of recommended product info
    recommended_products = []

    for product_id, row in top_products.iterrows():
        recommended_products.append({
            "id": product_id,
            "name": row['Product Name'],
        })

    return jsonify({
        "recommendations": recommended_products
    })


if __name__ == "__main__":
    app.run(debug=True)

