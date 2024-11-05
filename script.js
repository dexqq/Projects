var form = angular.module("form", []);
 
form.controller("formController", function($scope){
 
$scope.items = [
    {
        name: 'Content Management System',
        price: 500,
        active:true
    },
    {
        name: 'Document Management System',
        price: 400,
        active:false
    },
    {
        name: 'Membership System',
        price: 250,
        active:false
    },
    {
        name: 'Event Booking System',
        price: 120,
        active:true
        }
];

$scope.toggleActive = function(s){
    s.active = !s.active;
};
$scope.total = function(){
    var total = 0;

    angular.forEach($scope.items, function(s){
        if (s.active){
            total+= s.price;
        }
    });

    return total;
};
});