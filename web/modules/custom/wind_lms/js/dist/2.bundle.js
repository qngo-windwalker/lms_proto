(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[2],{

/***/ "./src/pages/content.js":
/*!******************************!*\
  !*** ./src/pages/content.js ***!
  \******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return ContentPage; });
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




var ContentPage = /*#__PURE__*/function (_React$Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(ContentPage, _React$Component);

  var _super = _createSuper(ContentPage);

  function ContentPage(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, ContentPage);

    _this = _super.call(this, props);
    _this.state = {
      liked: false
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(ContentPage, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        htmlFor: "address2"
      }, "Do you have existing content that you need converted?"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "custom-control custom-radio"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("input", {
        id: "credit",
        name: "paymentMethod",
        type: "radio",
        className: "custom-control-input",
        required: true
      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        className: "custom-control-label",
        htmlFor: "credit"
      }, "Yes", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("span", {
        className: "text-muted"
      }, " ( if yes Select the type of content best aligns with what you have \u2013select all that apply )"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "No, I need assistance sourcing content", /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("span", {
        className: "text-muted"
      }, " ( NOTE: Windwalker are not SMEs but can assist in identifying them for you. ) "))));
    }
  }]);

  return ContentPage;
}(react__WEBPACK_IMPORTED_MODULE_5___default.a.Component);



/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvcGFnZXMvY29udGVudC5qcyJdLCJuYW1lcyI6WyJDb250ZW50UGFnZSIsInByb3BzIiwic3RhdGUiLCJsaWtlZCIsIlJlYWN0IiwiQ29tcG9uZW50Il0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7O0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQWE7Ozs7Ozs7Ozs7OztBQUNiO0FBQ0E7O0lBU3FCQSxXOzs7OztBQUNuQix1QkFBWUMsS0FBWixFQUFtQjtBQUFBOztBQUFBOztBQUNqQiw4QkFBTUEsS0FBTjtBQUNBLFVBQUtDLEtBQUwsR0FBYTtBQUFFQyxXQUFLLEVBQUU7QUFBVCxLQUFiO0FBRmlCO0FBR2xCOzs7OzZCQUVRO0FBQ1AsMEJBQ0U7QUFBSyxpQkFBUyxFQUFDO0FBQWYsc0JBQ0k7QUFBTyxlQUFPLEVBQUM7QUFBZixpRUFESixlQUVJO0FBQUssaUJBQVMsRUFBQztBQUFmLHNCQUNFO0FBQU8sVUFBRSxFQUFDLFFBQVY7QUFBbUIsWUFBSSxFQUFDLGVBQXhCO0FBQXdDLFlBQUksRUFBQyxPQUE3QztBQUFxRCxpQkFBUyxFQUFDLHNCQUEvRDtBQUFzRixnQkFBUTtBQUE5RixRQURGLGVBRUU7QUFBTyxpQkFBUyxFQUFDLHNCQUFqQjtBQUF3QyxlQUFPLEVBQUM7QUFBaEQsNkJBQ0U7QUFBTSxpQkFBUyxFQUFDO0FBQWhCLDZHQURGLENBRkYsQ0FGSixlQVFJO0FBQUssaUJBQVMsRUFBQztBQUFmLHNCQUNFO0FBQU8sVUFBRSxFQUFDLE9BQVY7QUFBa0IsWUFBSSxFQUFDLGVBQXZCO0FBQXVDLFlBQUksRUFBQyxPQUE1QztBQUFvRCxpQkFBUyxFQUFDLHNCQUE5RDtBQUFxRixnQkFBUTtBQUE3RixRQURGLGVBRUU7QUFBTyxpQkFBUyxFQUFDLHNCQUFqQjtBQUF3QyxlQUFPLEVBQUM7QUFBaEQsZ0VBQ0U7QUFBTSxpQkFBUyxFQUFDO0FBQWhCLDJGQURGLENBRkYsQ0FSSixDQURGO0FBaUJEOzs7O0VBeEJzQ0MsNENBQUssQ0FBQ0MsUyIsImZpbGUiOiIyLmJ1bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbIlxyXG4ndXNlIHN0cmljdCc7XHJcbmltcG9ydCBSZWFjdCBmcm9tIFwicmVhY3RcIjtcclxuaW1wb3J0IHtcclxuICBCcm93c2VyUm91dGVyIGFzIFJvdXRlcixcclxuICBTd2l0Y2gsXHJcbiAgUm91dGUsXHJcbiAgSGFzaFJvdXRlcixcclxuICBMaW5rXHJcbn0gZnJvbSBcInJlYWN0LXJvdXRlci1kb21cIjtcclxuXHJcblxyXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBDb250ZW50UGFnZSBleHRlbmRzIFJlYWN0LkNvbXBvbmVudCB7XHJcbiAgY29uc3RydWN0b3IocHJvcHMpIHtcclxuICAgIHN1cGVyKHByb3BzKTtcclxuICAgIHRoaXMuc3RhdGUgPSB7IGxpa2VkOiBmYWxzZSB9O1xyXG4gIH1cclxuXHJcbiAgcmVuZGVyKCkge1xyXG4gICAgcmV0dXJuKFxyXG4gICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1iLTNcIj5cclxuICAgICAgICAgIDxsYWJlbCBodG1sRm9yPVwiYWRkcmVzczJcIj5EbyB5b3UgaGF2ZSBleGlzdGluZyBjb250ZW50IHRoYXQgeW91IG5lZWQgY29udmVydGVkPzwvbGFiZWw+XHJcbiAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sIGN1c3RvbS1yYWRpb1wiPlxyXG4gICAgICAgICAgICA8aW5wdXQgaWQ9XCJjcmVkaXRcIiBuYW1lPVwicGF5bWVudE1ldGhvZFwiIHR5cGU9XCJyYWRpb1wiIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWlucHV0XCIgcmVxdWlyZWQgLz5cclxuICAgICAgICAgICAgPGxhYmVsIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWxhYmVsXCIgaHRtbEZvcj1cImNyZWRpdFwiPlllcyBcclxuICAgICAgICAgICAgICA8c3BhbiBjbGFzc05hbWU9XCJ0ZXh0LW11dGVkXCI+ICggaWYgeWVzIFNlbGVjdCB0aGUgdHlwZSBvZiBjb250ZW50IGJlc3QgYWxpZ25zIHdpdGggd2hhdCB5b3UgaGF2ZSDigJNzZWxlY3QgYWxsIHRoYXQgYXBwbHkgKTwvc3Bhbj5cclxuICAgICAgICAgICAgPC9sYWJlbD5cclxuICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbCBjdXN0b20tcmFkaW9cIj5cclxuICAgICAgICAgICAgPGlucHV0IGlkPVwiZGViaXRcIiBuYW1lPVwicGF5bWVudE1ldGhvZFwiIHR5cGU9XCJyYWRpb1wiIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWlucHV0XCIgcmVxdWlyZWQgLz5cclxuICAgICAgICAgICAgPGxhYmVsIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWxhYmVsXCIgaHRtbEZvcj1cImRlYml0XCI+Tm8sIEkgbmVlZCBhc3Npc3RhbmNlIHNvdXJjaW5nIGNvbnRlbnQgXHJcbiAgICAgICAgICAgICAgPHNwYW4gY2xhc3NOYW1lPVwidGV4dC1tdXRlZFwiPiAoIE5PVEU6IFdpbmR3YWxrZXIgYXJlIG5vdCBTTUVzIGJ1dCBjYW4gYXNzaXN0IGluIGlkZW50aWZ5aW5nIHRoZW0gZm9yIHlvdS4gKSA8L3NwYW4+XHJcbiAgICAgICAgICAgIDwvbGFiZWw+XHJcbiAgICAgICAgICA8L2Rpdj5cclxuICAgICAgPC9kaXY+ICBcclxuICAgICk7XHJcbiAgfVxyXG59XHJcblxyXG4iXSwic291cmNlUm9vdCI6IiJ9