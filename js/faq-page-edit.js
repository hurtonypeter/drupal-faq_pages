(function($) {
  "use strict";

  var guid = (function() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
              .toString(16)
              .substring(1);
    }
    return function() {
      return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
              s4() + '-' + s4() + s4() + s4();
    };
  })();

  var app = angular.module('probaApp', ['ngDragDrop']);
  app.value('$', $);

  //config start and end symbol not cause conflict with twig
  app.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  app.controller('probaCtrl', function($scope) {
    $scope.terms = $.map(drupalSettings.term_model, function(value, index) {
      return [value];
    });

    $scope.title = "muhahhahaha";
    $scope.url = "/sdf";
    $scope.description = "little descrp";
    $scope.blocks = [
      {id: 1, name: 'blokk1', description: 'blockdecrtiption1', terms: []},
      {id: 2, name: 'blocck2', description: 'blockdecrtiption2', terms: []},
      {id: 3, name: 'blokk3', description: 'blockdecrtiption3', terms: []}
    ];

    $scope.newBlock = function() {
      $scope.blocks.push({
        id: null,
        name: '',
        description: '',
        terms: [],
      });
    };

    $scope.deleteBlock = function(id) {
      $scope.blocks.splice(id, 1);
    };

    $scope.blockUp = function(block) {
      var temp = $scope.blocks[block - 1];
      $scope.blocks[block - 1] = $scope.blocks[block];
      $scope.blocks[block] = temp;
    };

    $scope.blockDown = function(block) {
      var temp = $scope.blocks[block + 1];
      $scope.blocks[block + 1] = $scope.blocks[block];
      $scope.blocks[block] = temp;
    };

    $scope.termUp = function(term, block) {
      var temp = $scope.blocks[block].terms[term - 1];
      $scope.blocks[block].terms[term - 1] = $scope.blocks[block].terms[term];
      $scope.blocks[block].terms[term] = temp;
    };

    $scope.termDown = function(term, block) {
      var temp = $scope.blocks[block].terms[term + 1];
      $scope.blocks[block].terms[term + 1] = $scope.blocks[block].terms[term];
      $scope.blocks[block].terms[term] = temp;
    };
    
    $scope.deleteTerm = function(term, block) {
      $scope.blocks[block].terms.splice(term, 1);
    };

    $scope.hideMe = function(terms) {
      return terms.length == 0;
    };
    
  });

})(jQuery);