var url = new URL(window.location.href);
var catSelected = parseInt(url.searchParams.get('getGoodsByCat'));
if (catSelected === undefined || isNaN(catSelected)) {
    catSelected = 0;
}
var sortingSelected = url.searchParams.get('sorting');
if (sortingSelected === undefined || sortingSelected===null) {
    sortingSelected = 'default';
}

window.onload = function () {
    console.log('WindowLoaded');
    document.getElementById('goodsSortingSelect').value = sortingSelected;
    loadCategories();
    loadGoods();
};

function loadCategories() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        var catDiv = document.getElementById('categories');
        var listElement = document.createElement('ul');
        var listArray = JSON.parse(this.responseText);
        listArray.forEach(addCatToList);

        function addCatToList(item, index) {
            var li = document.createElement('li');
            li.innerHTML = item['name'] + ' (' + item['goodsCount'] + ')';
            li.setAttribute('onclick', 'showGoodsByCat(' + item['id'] + ')');
            if (index == catSelected) {
                li.setAttribute('class', 'catSelected');
            } else {
                li.setAttribute('class', 'catNotSelected');
            }
            li.id = 'cat' + item['id'];
            listElement.appendChild(li);
        }

        catDiv.appendChild(listElement);
    }
    xhttp.open("GET", "ajax.php?getCat", true);
    xhttp.send();
}

function showGoodsByCat(catID) {
    catSelected = catID;
    document.getElementById('goods').innerHTML = '';
    var categories = document.getElementById('categories').getElementsByTagName('li');
    for (var i = 0; i < categories.length; i++) {
        if (categories.item(i).id == 'cat' + catID) {
            categories.item(i).className = 'catSelected';
        } else {
            categories.item(i).className = 'catNotSelected';
        }
    }
    loadGoods();
}

function loadGoods() {
    url.searchParams.set('getGoodsByCat', catSelected);
    url.searchParams.set('sorting', sortingSelected);
    window.history.pushState('', '', url.href);
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        var goodsDiv = document.getElementById('goods');
        var listElement = document.createElement('ul');
        var listArray = JSON.parse(this.responseText);
        listArray.forEach(addGoodsToList);

        function addGoodsToList(item) {
            var li = document.createElement('li');
            li.innerHTML = item['name'] + '<br>' + item['price'] + '<br>' + item['date'] +
                '<br><button type="button" class="btn btn-primary buyItem" data-bs-toggle="modal" data-bs-target="#buyGoods" onclick="loadItem(' + item['id'] + ')">Придбати</button><hr>';
            listElement.appendChild(li);
        }

        goodsDiv.appendChild(listElement);

    }
    xhttp.open("GET", "ajax.php?getGoodsByCat=" + catSelected + '&sorting=' + sortingSelected, true);
    xhttp.send();

}

function loadItem(id) {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function () {
        var modalBody = document.getElementById('modal-body');
        var itemInfo = JSON.parse(this.responseText);
        modalBody.innerHTML = itemInfo['name'] + '<br/>' + itemInfo['price'] + '<br/>' + itemInfo['date'];
    }
    xhttp.open("GET", "ajax.php?getItemInfo=" + id, true);
    xhttp.send();

}

function goodsSorting() {
    document.getElementById('goods').innerHTML = '';
    sortingSelected = document.getElementById('goodsSortingSelect').value;
    loadGoods();
}