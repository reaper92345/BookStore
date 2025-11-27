import requests
import uuid
import sys

BASE_URL = "http://localhost:8000"

def test_cart_flow():
    # Generate a random cart ID
    cart_id = f"test_cart_{uuid.uuid4()}"
    print(f"Testing with Cart ID: {cart_id}")

    # 1. Add item to cart
    print("\n1. Adding item to cart...")
    payload = {
        "cart_id": cart_id,
        "book_id": 1,  # Assuming book with ID 1 exists
        "quantity": 1
    }
    try:
        response = requests.post(f"{BASE_URL}/cart/", json=payload)
        if response.status_code == 200:
            print("   Success:", response.json())
        else:
            print(f"   Failed: {response.status_code} - {response.text}")
            return
    except requests.exceptions.ConnectionError:
        print("   Failed: Could not connect to backend. Is it running?")
        return

    # 2. Get cart
    print("\n2. Fetching cart...")
    try:
        response = requests.get(f"{BASE_URL}/cart/", params={"cart_id": cart_id})
        if response.status_code == 200:
            cart_items = response.json()
            print(f"   Cart Items: {len(cart_items)}")
            for item in cart_items:
                print(f"   - Book ID: {item['book_id']}, Qty: {item['quantity']}")
            
            if len(cart_items) > 0 and cart_items[0]['book_id'] == 1:
                print("   Verification: Item found in cart.")
            else:
                print("   Verification: Item NOT found in cart.")
        else:
            print(f"   Failed: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"   Failed: {e}")

    # 3. Add same item again (update quantity)
    print("\n3. Adding same item again...")
    try:
        response = requests.post(f"{BASE_URL}/cart/", json=payload)
        if response.status_code == 200:
            print("   Success:", response.json())
        else:
            print(f"   Failed: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"   Failed: {e}")

    # 4. Get cart again
    print("\n4. Fetching cart again...")
    try:
        response = requests.get(f"{BASE_URL}/cart/", params={"cart_id": cart_id})
        if response.status_code == 200:
            cart_items = response.json()
            for item in cart_items:
                print(f"   - Book ID: {item['book_id']}, Qty: {item['quantity']}")
            
            if len(cart_items) > 0 and cart_items[0]['quantity'] == 2:
                print("   Verification: Quantity updated correctly.")
            else:
                print("   Verification: Quantity NOT updated.")
        else:
            print(f"   Failed: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"   Failed: {e}")

if __name__ == "__main__":
    test_cart_flow()
