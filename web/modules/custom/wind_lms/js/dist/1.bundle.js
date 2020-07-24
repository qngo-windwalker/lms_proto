(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[1],{

/***/ "./src/pages/audio.js":
/*!****************************!*\
  !*** ./src/pages/audio.js ***!
  \****************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return AudioPage; });
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




var AudioPage = /*#__PURE__*/function (_React$Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_2___default()(AudioPage, _React$Component);

  var _super = _createSuper(AudioPage);

  function AudioPage(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, AudioPage);

    _this = _super.call(this, props);
    _this.state = {
      liked: false
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(AudioPage, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(react__WEBPACK_IMPORTED_MODULE_5___default.a.Fragment, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        htmlFor: "address"
      }, "Do you require audio voice over?  "), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "Yes")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "No "))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        htmlFor: "address"
      }, "Do you require custom animation? (use example) "), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "Yes If \u201CYes\u201D how many minutes of animation? ")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "No "))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("label", {
        htmlFor: "address"
      }, "Are you interested in Video services? (use example) "), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "Yes  If \u201CYes\u201D how many minutes of video? ")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement("div", {
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
      }, "No "))));
    }
  }]);

  return AudioPage;
}(react__WEBPACK_IMPORTED_MODULE_5___default.a.Component);



/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvcGFnZXMvYXVkaW8uanMiXSwibmFtZXMiOlsiQXVkaW9QYWdlIiwicHJvcHMiLCJzdGF0ZSIsImxpa2VkIiwiUmVhY3QiLCJDb21wb25lbnQiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBYTs7Ozs7Ozs7Ozs7O0FBQ2I7QUFDQTs7SUFTcUJBLFM7Ozs7O0FBQ25CLHFCQUFZQyxLQUFaLEVBQW1CO0FBQUE7O0FBQUE7O0FBQ2pCLDhCQUFNQSxLQUFOO0FBQ0EsVUFBS0MsS0FBTCxHQUFhO0FBQUVDLFdBQUssRUFBRTtBQUFULEtBQWI7QUFGaUI7QUFHbEI7Ozs7NkJBRVE7QUFDUCwwQkFDRSxxSUFDRTtBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLGVBQU8sRUFBQztBQUFmLDhDQURGLGVBRUU7QUFBSyxpQkFBUyxFQUFDO0FBQWYsc0JBQ0U7QUFBTyxVQUFFLEVBQUMsUUFBVjtBQUFtQixZQUFJLEVBQUMsZUFBeEI7QUFBd0MsWUFBSSxFQUFDLE9BQTdDO0FBQXFELGlCQUFTLEVBQUMsc0JBQS9EO0FBQXNGLGVBQU8sTUFBN0Y7QUFBOEYsZ0JBQVE7QUFBdEcsUUFERixlQUVFO0FBQU8saUJBQVMsRUFBQyxzQkFBakI7QUFBd0MsZUFBTyxFQUFDO0FBQWhELGVBRkYsQ0FGRixlQU1FO0FBQUssaUJBQVMsRUFBQztBQUFmLHNCQUNFO0FBQU8sVUFBRSxFQUFDLE9BQVY7QUFBa0IsWUFBSSxFQUFDLGVBQXZCO0FBQXVDLFlBQUksRUFBQyxPQUE1QztBQUFvRCxpQkFBUyxFQUFDLHNCQUE5RDtBQUFxRixnQkFBUTtBQUE3RixRQURGLGVBRUU7QUFBTyxpQkFBUyxFQUFDLHNCQUFqQjtBQUF3QyxlQUFPLEVBQUM7QUFBaEQsZUFGRixDQU5GLENBREYsZUFhRTtBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLGVBQU8sRUFBQztBQUFmLDJEQURGLGVBRUU7QUFBSyxpQkFBUyxFQUFDO0FBQWYsc0JBQ0U7QUFBTyxVQUFFLEVBQUMsUUFBVjtBQUFtQixZQUFJLEVBQUMsZUFBeEI7QUFBd0MsWUFBSSxFQUFDLE9BQTdDO0FBQXFELGlCQUFTLEVBQUMsc0JBQS9EO0FBQXNGLGVBQU8sTUFBN0Y7QUFBOEYsZ0JBQVE7QUFBdEcsUUFERixlQUVFO0FBQU8saUJBQVMsRUFBQyxzQkFBakI7QUFBd0MsZUFBTyxFQUFDO0FBQWhELGtFQUZGLENBRkYsZUFNRTtBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLFVBQUUsRUFBQyxPQUFWO0FBQWtCLFlBQUksRUFBQyxlQUF2QjtBQUF1QyxZQUFJLEVBQUMsT0FBNUM7QUFBb0QsaUJBQVMsRUFBQyxzQkFBOUQ7QUFBcUYsZ0JBQVE7QUFBN0YsUUFERixlQUVFO0FBQU8saUJBQVMsRUFBQyxzQkFBakI7QUFBd0MsZUFBTyxFQUFDO0FBQWhELGVBRkYsQ0FORixDQWJGLGVBeUJHO0FBQUssaUJBQVMsRUFBQztBQUFmLHNCQUNDO0FBQU8sZUFBTyxFQUFDO0FBQWYsZ0VBREQsZUFFQztBQUFLLGlCQUFTLEVBQUM7QUFBZixzQkFDRTtBQUFPLFVBQUUsRUFBQyxRQUFWO0FBQW1CLFlBQUksRUFBQyxlQUF4QjtBQUF3QyxZQUFJLEVBQUMsT0FBN0M7QUFBcUQsaUJBQVMsRUFBQyxzQkFBL0Q7QUFBc0YsZUFBTyxNQUE3RjtBQUE4RixnQkFBUTtBQUF0RyxRQURGLGVBRUU7QUFBTyxpQkFBUyxFQUFDLHNCQUFqQjtBQUF3QyxlQUFPLEVBQUM7QUFBaEQsK0RBRkYsQ0FGRCxlQU1DO0FBQUssaUJBQVMsRUFBQztBQUFmLHNCQUNFO0FBQU8sVUFBRSxFQUFDLE9BQVY7QUFBa0IsWUFBSSxFQUFDLGVBQXZCO0FBQXVDLFlBQUksRUFBQyxPQUE1QztBQUFvRCxpQkFBUyxFQUFDLHNCQUE5RDtBQUFxRixnQkFBUTtBQUE3RixRQURGLGVBRUU7QUFBTyxpQkFBUyxFQUFDLHNCQUFqQjtBQUF3QyxlQUFPLEVBQUM7QUFBaEQsZUFGRixDQU5ELENBekJILENBREY7QUF1Q0Q7Ozs7RUE5Q29DQyw0Q0FBSyxDQUFDQyxTIiwiZmlsZSI6IjEuYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiXHJcbid1c2Ugc3RyaWN0JztcclxuaW1wb3J0IFJlYWN0IGZyb20gXCJyZWFjdFwiO1xyXG5pbXBvcnQge1xyXG4gIEJyb3dzZXJSb3V0ZXIgYXMgUm91dGVyLFxyXG4gIFN3aXRjaCxcclxuICBSb3V0ZSxcclxuICBIYXNoUm91dGVyLFxyXG4gIExpbmtcclxufSBmcm9tIFwicmVhY3Qtcm91dGVyLWRvbVwiO1xyXG5cclxuXHJcbmV4cG9ydCBkZWZhdWx0IGNsYXNzIEF1ZGlvUGFnZSBleHRlbmRzIFJlYWN0LkNvbXBvbmVudCB7XHJcbiAgY29uc3RydWN0b3IocHJvcHMpIHtcclxuICAgIHN1cGVyKHByb3BzKTtcclxuICAgIHRoaXMuc3RhdGUgPSB7IGxpa2VkOiBmYWxzZSB9O1xyXG4gIH1cclxuXHJcbiAgcmVuZGVyKCkge1xyXG4gICAgcmV0dXJuKFxyXG4gICAgICA8PlxyXG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwibWItM1wiPlxyXG4gICAgICAgICAgPGxhYmVsIGh0bWxGb3I9XCJhZGRyZXNzXCI+RG8geW91IHJlcXVpcmUgYXVkaW8gdm9pY2Ugb3Zlcj8gIDwvbGFiZWw+XHJcbiAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sIGN1c3RvbS1yYWRpb1wiPlxyXG4gICAgICAgICAgICA8aW5wdXQgaWQ9XCJjcmVkaXRcIiBuYW1lPVwicGF5bWVudE1ldGhvZFwiIHR5cGU9XCJyYWRpb1wiIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWlucHV0XCIgY2hlY2tlZCByZXF1aXJlZCAvPlxyXG4gICAgICAgICAgICA8bGFiZWwgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wtbGFiZWxcIiBodG1sRm9yPVwiY3JlZGl0XCI+WWVzPC9sYWJlbD5cclxuICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbCBjdXN0b20tcmFkaW9cIj5cclxuICAgICAgICAgICAgPGlucHV0IGlkPVwiZGViaXRcIiBuYW1lPVwicGF5bWVudE1ldGhvZFwiIHR5cGU9XCJyYWRpb1wiIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWlucHV0XCIgcmVxdWlyZWQgLz5cclxuICAgICAgICAgICAgPGxhYmVsIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWxhYmVsXCIgaHRtbEZvcj1cImRlYml0XCI+Tm8gPC9sYWJlbD5cclxuICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgIDwvZGl2PlxyXG5cclxuICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1iLTNcIj5cclxuICAgICAgICAgIDxsYWJlbCBodG1sRm9yPVwiYWRkcmVzc1wiPkRvIHlvdSByZXF1aXJlIGN1c3RvbSBhbmltYXRpb24/ICh1c2UgZXhhbXBsZSkgPC9sYWJlbD5cclxuICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wgY3VzdG9tLXJhZGlvXCI+XHJcbiAgICAgICAgICAgIDxpbnB1dCBpZD1cImNyZWRpdFwiIG5hbWU9XCJwYXltZW50TWV0aG9kXCIgdHlwZT1cInJhZGlvXCIgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wtaW5wdXRcIiBjaGVja2VkIHJlcXVpcmVkIC8+XHJcbiAgICAgICAgICAgIDxsYWJlbCBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1sYWJlbFwiIGh0bWxGb3I9XCJjcmVkaXRcIj5ZZXMgSWYg4oCcWWVz4oCdIGhvdyBtYW55IG1pbnV0ZXMgb2YgYW5pbWF0aW9uPyA8L2xhYmVsPlxyXG4gICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sIGN1c3RvbS1yYWRpb1wiPlxyXG4gICAgICAgICAgICA8aW5wdXQgaWQ9XCJkZWJpdFwiIG5hbWU9XCJwYXltZW50TWV0aG9kXCIgdHlwZT1cInJhZGlvXCIgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wtaW5wdXRcIiByZXF1aXJlZCAvPlxyXG4gICAgICAgICAgICA8bGFiZWwgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wtbGFiZWxcIiBodG1sRm9yPVwiZGViaXRcIj5ObyA8L2xhYmVsPlxyXG4gICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgPC9kaXY+XHJcblxyXG4gICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1iLTNcIj5cclxuICAgICAgICAgIDxsYWJlbCBodG1sRm9yPVwiYWRkcmVzc1wiPkFyZSB5b3UgaW50ZXJlc3RlZCBpbiBWaWRlbyBzZXJ2aWNlcz8gKHVzZSBleGFtcGxlKSA8L2xhYmVsPlxyXG4gICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbCBjdXN0b20tcmFkaW9cIj5cclxuICAgICAgICAgICAgPGlucHV0IGlkPVwiY3JlZGl0XCIgbmFtZT1cInBheW1lbnRNZXRob2RcIiB0eXBlPVwicmFkaW9cIiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1pbnB1dFwiIGNoZWNrZWQgcmVxdWlyZWQgLz5cclxuICAgICAgICAgICAgPGxhYmVsIGNsYXNzTmFtZT1cImN1c3RvbS1jb250cm9sLWxhYmVsXCIgaHRtbEZvcj1cImNyZWRpdFwiPlllcyAgSWYg4oCcWWVz4oCdIGhvdyBtYW55IG1pbnV0ZXMgb2YgdmlkZW8/IDwvbGFiZWw+XHJcbiAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiY3VzdG9tLWNvbnRyb2wgY3VzdG9tLXJhZGlvXCI+XHJcbiAgICAgICAgICAgIDxpbnB1dCBpZD1cImRlYml0XCIgbmFtZT1cInBheW1lbnRNZXRob2RcIiB0eXBlPVwicmFkaW9cIiBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1pbnB1dFwiIHJlcXVpcmVkIC8+XHJcbiAgICAgICAgICAgIDxsYWJlbCBjbGFzc05hbWU9XCJjdXN0b20tY29udHJvbC1sYWJlbFwiIGh0bWxGb3I9XCJkZWJpdFwiPk5vIDwvbGFiZWw+XHJcbiAgICAgICAgICA8L2Rpdj5cclxuICAgICAgPC9kaXY+ICBcclxuICAgICAgPC8+XHJcbiAgICApO1xyXG4gIH1cclxufVxyXG5cclxuIl0sInNvdXJjZVJvb3QiOiIifQ==