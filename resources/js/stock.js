document.addEventListener('DOMContentLoaded', function () {

    const product = document.querySelector('[name="product_id"]');
    const business = document.querySelector('[name="business_id"]');
    const date = document.querySelector('[name="date"]');

    function fetchStockData() {

        if (!product.value || !business.value || !date.value) return;

        console.log("Fetching data...");

        fetch(window.stockDataUrl + "?product_id=" + product.value + "&business_id=" + business.value + "&date=" + date.value)
            .then(response => response.json())
            .then(data => {

                console.log("DATA:", data);

                const opening = document.querySelector('[name="opening_stock"]');
                const purchased = document.querySelector('[name="purchased"]');
                const price = document.querySelector('[name="price"]');

                if (opening) opening.value = data.opening_stock ?? 0;
                if (purchased) purchased.value = data.purchased ?? 0;
                if (price) price.value = data.price ?? 0;

            })
            .catch(error => console.error("ERROR:", error));
    }

    if (product) {
        product.addEventListener('change', fetchStockData);
    }

});