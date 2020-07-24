(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[5],{

/***/ "./src/pages/graphics.js":
/*!*******************************!*\
  !*** ./src/pages/graphics.js ***!
  \*******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return GraphicsPage; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react_router_dom__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react-router-dom */ "./node_modules/react-router-dom/esm/react-router-dom.js");








function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }




var GraphicsPage = /*#__PURE__*/function (_React$Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(GraphicsPage, _React$Component);

  var _super = _createSuper(GraphicsPage);

  function GraphicsPage(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, GraphicsPage);

    _this = _super.call(this, props);
    _this.state = {
      liked: false
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(GraphicsPage, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        htmlFor: "address"
      }, "Do you require custom graphics? "), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "custom-control custom-radio"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("input", {
        id: "credit",
        name: "paymentMethod",
        type: "radio",
        className: "custom-control-input",
        checked: true,
        required: true
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        className: "custom-control-label",
        htmlFor: "credit"
      }, "Yes, I need graphics to be created", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("span", {
        className: "text-muted"
      }, "(If yes what type)"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "custom-control custom-radio"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("input", {
        id: "debit",
        name: "paymentMethod",
        type: "radio",
        className: "custom-control-input",
        required: true
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        className: "custom-control-label",
        htmlFor: "debit"
      }, "No, I will provide all graphics ")));
    }
  }]);

  return GraphicsPage;
}(react__WEBPACK_IMPORTED_MODULE_5___default.a.Component);



/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvcGFnZXMvZ3JhcGhpY3MuanMiXSwibmFtZXMiOlsiR3JhcGhpY3NQYWdlIiwicHJvcHMiLCJzdGF0ZSIsImxpa2VkIiwiUmVhY3QiLCJDb21wb25lbnQiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBYTs7Ozs7Ozs7Ozs7O0FBQ2I7QUFDQTs7SUFTcUJBLFk7Ozs7O0FBQ25CLHdCQUFZQyxLQUFaLEVBQW1CO0FBQUE7O0FBQUE7O0FBQ2pCLDhCQUFNQSxLQUFOO0FBQ0EsVUFBS0MsS0FBTCxHQUFhO0FBQUVDLFdBQUssRUFBRTtBQUFULEtBQWI7QUFGaUI7QUFHbEI7Ozs7NkJBRVE7QUFDUCwwQkFDRTtBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLGVBQU8sRUFBQztBQUFmLDRDQURGLGVBRUU7QUFBSyxpQkFBUyxFQUFDO0FBQWYsc0JBQ0U7QUFBTyxVQUFFLEVBQUMsUUFBVjtBQUFtQixZQUFJLEVBQUMsZUFBeEI7QUFBd0MsWUFBSSxFQUFDLE9BQTdDO0FBQXFELGlCQUFTLEVBQUMsc0JBQS9EO0FBQXNGLGVBQU8sTUFBN0Y7QUFBOEYsZ0JBQVE7QUFBdEcsUUFERixlQUVFO0FBQU8saUJBQVMsRUFBQyxzQkFBakI7QUFBd0MsZUFBTyxFQUFDO0FBQWhELDREQUNFO0FBQU0saUJBQVMsRUFBQztBQUFoQiw4QkFERixDQUZGLENBRkYsZUFRRTtBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLFVBQUUsRUFBQyxPQUFWO0FBQWtCLFlBQUksRUFBQyxlQUF2QjtBQUF1QyxZQUFJLEVBQUMsT0FBNUM7QUFBb0QsaUJBQVMsRUFBQyxzQkFBOUQ7QUFBcUYsZ0JBQVE7QUFBN0YsUUFERixlQUVFO0FBQU8saUJBQVMsRUFBQyxzQkFBakI7QUFBd0MsZUFBTyxFQUFDO0FBQWhELDRDQUZGLENBUkYsQ0FERjtBQWdCRDs7OztFQXZCdUNDLDRDQUFLLENBQUNDLFMiLCJmaWxlIjoiNS5idW5kbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyJcclxuJ3VzZSBzdHJpY3QnO1xyXG5pbXBvcnQgUmVhY3QgZnJvbSBcInJlYWN0XCI7XHJcbmltcG9ydCB7XHJcbiAgQnJvd3NlclJvdXRlciBhcyBSb3V0ZXIsXHJcbiAgU3dpdGNoLFxyXG4gIFJvdXRlLFxyXG4gIEhhc2hSb3V0ZXIsXHJcbiAgTGlua1xyXG59IGZyb20gXCJyZWFjdC1yb3V0ZXItZG9tXCI7XHJcblxyXG5cclxuZXhwb3J0IGRlZmF1bHQgY2xhc3MgR3JhcGhpY3NQYWdlIGV4dGVuZHMgUmVhY3QuQ29tcG9uZW50IHtcclxuICBjb25zdHJ1Y3Rvcihwcm9wcykge1xyXG4gICAgc3VwZXIocHJvcHMpO1xyXG4gICAgdGhpcy5zdGF0ZSA9IHsgbGlrZWQ6IGZhbHNlIH07XHJcbiAgfVxyXG5cclxuICByZW5kZXIoKSB7XHJcbiAgICByZXR1cm4oXHJcbiAgICAgIDxkaXYgY2xhc3NOYW1lPVwibWItM1wiPlxyXG4gICAgICAgIDxsYWJlbCBodG1sRm9yPVwiYWRkcmVzc1wiPkRvIHlvdSByZXF1aXJlIGN1c3RvbSBncmFwaGljcz8gPC9sYWJlbD5cclxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sIGN1c3RvbS1yYWRpb1wiPlxyXG4gICAgICAgICAgPGlucHV0IGlkPVwiY3JlZGl0XCIgbmFtZT1cInBheW1lbnRNZXRob2RcIiB0eXBlPVwicmFkaW9cIiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1pbnB1dFwiIGNoZWNrZWQgcmVxdWlyZWQgLz5cclxuICAgICAgICAgIDxsYWJlbCBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1sYWJlbFwiIGh0bWxGb3I9XCJjcmVkaXRcIj5ZZXMsIEkgbmVlZCBncmFwaGljcyB0byBiZSBjcmVhdGVkICBcclxuICAgICAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidGV4dC1tdXRlZFwiPihJZiB5ZXMgd2hhdCB0eXBlKTwvc3Bhbj5cclxuICAgICAgICAgIDwvbGFiZWw+XHJcbiAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbCBjdXN0b20tcmFkaW9cIj5cclxuICAgICAgICAgIDxpbnB1dCBpZD1cImRlYml0XCIgbmFtZT1cInBheW1lbnRNZXRob2RcIiB0eXBlPVwicmFkaW9cIiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1pbnB1dFwiIHJlcXVpcmVkIC8+XHJcbiAgICAgICAgICA8bGFiZWwgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wtbGFiZWxcIiBodG1sRm9yPVwiZGViaXRcIj5ObywgSSB3aWxsIHByb3ZpZGUgYWxsIGdyYXBoaWNzIDwvbGFiZWw+XHJcbiAgICAgICAgPC9kaXY+XHJcbiAgICAgIDwvZGl2PlxyXG5cclxuICAgICk7XHJcbiAgfVxyXG59XHJcblxyXG4iXSwic291cmNlUm9vdCI6IiJ9