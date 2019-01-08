<?php

/* @var $this yii\web\View */

$this->title = 'Search products';
?>
<div class="site-index">

    <div class="jumbotron">

        <div class="container">
            <br/>
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card card-sm">
                        <div class="card-body row no-gutters align-items-center">
                            <div class="col-auto">
                                <i id="clear" class="fas fa-search h4 text-body"></i>
                            </div>
                            <!--end of col-->
                            <div class="col">
                                <input id="searchInput" class="form-control form-control-lg form-control-borderless" type="search" placeholder="Search products">
                            </div>
                            <!--end of col-->
                            <div class="col-auto">
                                <button id="searchButton" class="btn btn-lg btn-success" type="button">Search</button>
                            </div>
                            <!--end of col-->
                        </div>
                    </div>
                    <div id="searchedProducts"></div>
                </div>
                <!--end of col-->
            </div>
        </div>

    </div>

</div>


<script>
    var searchWaitReached = true;
    var search = {
        init: function() {
            search.keyPress();
            search.clickSearchButton();
            search.clear();
        },
        clear: function() {
            document.getElementById("clear").onclick = function () {
                document.getElementById("searchInput").value = '';
                document.getElementById("searchedProducts").innerHTML = '';
            }
        },
        keyPress : function () {
            document.getElementById("searchInput").onkeydown2 = function (event) {
                var s = document.getElementById("searchInput").value;
                console.log('keydown: ' + s);
            };

            document.getElementById("searchInput").onkeyup2 = function (event) {
                var s = document.getElementById("searchInput").value;
                console.log('keyup: ' + s + ' key code : ' + event.keyCode);
            };

            document.getElementById("searchInput").onkeypress2 = function (event) {
                var s = document.getElementById("searchInput").value;
                console.log('keypress: ' + s);
            };

            document.getElementById("searchInput").onkeyup = function (event) {
                var s = document.getElementById("searchInput").value;
                if(event.keyCode == 13 || event.which == 13 || s.length > 2 ) {
                    // event.preventDefault();
                    search.doSearch(s);
                    searchWaitReached = false;
                }
            };
        },
        clickSearchButton : function () {
            document.getElementById('searchButton').onclick = function () {
                event.preventDefault();
                var s = document.getElementById("searchInput").value;
                search.doSearch(s);
            };
        },
        render : function (p) {
            var products = JSON.parse(p);
            var html = '';
            products.forEach(function (product) {
                html += search.renderProduct(product);
            });
            document.getElementById("searchedProducts").innerHTML = html;
        },
        renderProduct: function (product) {
            var html = '';
            html = '<div>'
                + '<b>'+ product.name + '</b>'
                + '<p>'+ product.category
                + '<span class="text-xs badge badge-light float-right">relevance: '+ product.relevance +'</span>'
                + '</p>'
                + '<span>'

                + '</span>'
                + '</div>';

            return html;
        },
        doSearch : function (s) {
            if(searchWaitReached == false ) {
                return false;
            };

            var xmlHttp = new XMLHttpRequest();

            xmlHttp.onreadystatechange = function() {
                if (xmlHttp.readyState == XMLHttpRequest.DONE) {
                    if (xmlHttp.status == 200) {
                        search.render(xmlHttp.responseText);
                    }
                    else if (xmlHttp.status == 400) {
                        alert('There was an error 400, Please refresh the page.');
                    }
                    else {
                        alert('something else other than 200 was returned, Please refresh the page.');
                    }
                }
            };

            xmlHttp.open("GET", "search/do?s="+s, true);
            xmlHttp.send();

            setTimeout(function () {
                searchWaitReached = true;
            }, 500);
        }
    };

    search.init();
</script>
