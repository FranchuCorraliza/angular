angular.module('librosApp')
.controller('TodosLosLibrosCtrl',function ($scope, LibrosService) {
	$scope.libros = LibrosService.todosLosLibros();
});
  
  
