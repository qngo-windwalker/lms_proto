(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[7],{

/***/ "./src/pages/interested.js":
/*!*********************************!*\
  !*** ./src/pages/interested.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var react_router_dom__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react-router-dom */ "./node_modules/react-router-dom/esm/react-router-dom.js");
/* harmony import */ var react_redux__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! react-redux */ "./node_modules/react-redux/es/index.js");
/* harmony import */ var _actions_productsActions__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../actions/productsActions */ "./src/actions/productsActions.js");
/* harmony import */ var _actions_userActions__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../actions/userActions */ "./src/actions/userActions.js");









function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_5___default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_5___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_4___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }







var mapStateToProps = function mapStateToProps(state) {
  return {
    products: state.products,
    user: state.user
  };
}; // To allow automatically dispatch Redux Action to the Redux Store


var mapActionsToProps = {
  onUpdateProduct: _actions_productsActions__WEBPACK_IMPORTED_MODULE_9__["updateProduct"],
  onUpdateUser: _actions_userActions__WEBPACK_IMPORTED_MODULE_10__["updateUser"]
};

var InterestedPage = /*#__PURE__*/function (_React$Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_3___default()(InterestedPage, _React$Component);

  var _super = _createSuper(InterestedPage);

  function InterestedPage(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, InterestedPage);

    _this = _super.call(this, props);
    _this.state = {
      liked: false
    };
    _this.onUpdateProduct = _this.onUpdateProduct.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_2___default()(_this));
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(InterestedPage, [{
    key: "onUpdateProduct",
    value: function onUpdateProduct(event) {
      // This method defined in mapActionsToProps object
      this.props.onUpdateProduct({
        name: event.currentTarget.value
      });
    }
  }, {
    key: "render",
    value: function render() {
      console.log(this.props);
      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("div", {
        className: "mb-3"
      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("h6", null, "I am interested in: "), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("ul", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("button", {
        onClick: this.onUpdateProduct,
        value: "E-Learning"
      }, "E-Learning ")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("button", {
        onClick: this.onUpdateProduct,
        value: "Classroom training"
      }, "Classroom training ")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("button", {
        onClick: this.onUpdateProduct,
        value: "Virtual Instructor Led Training"
      }, "Virtual Instructor Led Training ")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("li", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("button", {
        onClick: this.onUpdateProduct,
        value: "Other"
      }, "Other (example podcast, infographics, posters) "))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("div", null, "Selected: ", this.props.products.map(function (product, index) {
        return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_6___default.a.createElement("div", {
          key: index
        }, product.name);
      })));
    }
  }]);

  return InterestedPage;
}(react__WEBPACK_IMPORTED_MODULE_6___default.a.Component); // wrap InterestedPage in connect and pass in mapStateToProps
// export default connect(mapStateToProps)(InterestedPage)


/* harmony default export */ __webpack_exports__["default"] = (Object(react_redux__WEBPACK_IMPORTED_MODULE_8__["connect"])(mapStateToProps, mapActionsToProps)(InterestedPage));

/***/ })

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvcGFnZXMvaW50ZXJlc3RlZC5qcyJdLCJuYW1lcyI6WyJtYXBTdGF0ZVRvUHJvcHMiLCJzdGF0ZSIsInByb2R1Y3RzIiwidXNlciIsIm1hcEFjdGlvbnNUb1Byb3BzIiwib25VcGRhdGVQcm9kdWN0IiwidXBkYXRlUHJvZHVjdCIsIm9uVXBkYXRlVXNlciIsInVwZGF0ZVVzZXIiLCJJbnRlcmVzdGVkUGFnZSIsInByb3BzIiwibGlrZWQiLCJiaW5kIiwiZXZlbnQiLCJuYW1lIiwiY3VycmVudFRhcmdldCIsInZhbHVlIiwiY29uc29sZSIsImxvZyIsIm1hcCIsInByb2R1Y3QiLCJpbmRleCIsIlJlYWN0IiwiQ29tcG9uZW50IiwiY29ubmVjdCJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7OztBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQWE7Ozs7Ozs7Ozs7Ozs7QUFDYjtBQUNBO0FBUUE7QUFDQTtBQUNBOztBQUVBLElBQU1BLGVBQWUsR0FBRyxTQUFsQkEsZUFBa0IsQ0FBQUMsS0FBSztBQUFBLFNBQUs7QUFDaENDLFlBQVEsRUFBRUQsS0FBSyxDQUFDQyxRQURnQjtBQUVoQ0MsUUFBSSxFQUFFRixLQUFLLENBQUNFO0FBRm9CLEdBQUw7QUFBQSxDQUE3QixDLENBS0E7OztBQUNBLElBQU1DLGlCQUFpQixHQUFHO0FBQ3hCQyxpQkFBZSxFQUFHQyxzRUFETTtBQUV4QkMsY0FBWSxFQUFHQyxnRUFBVUE7QUFGRCxDQUExQjs7SUFNTUMsYzs7Ozs7QUFDSiwwQkFBWUMsS0FBWixFQUFtQjtBQUFBOztBQUFBOztBQUNqQiw4QkFBTUEsS0FBTjtBQUNBLFVBQUtULEtBQUwsR0FBYTtBQUFFVSxXQUFLLEVBQUU7QUFBVCxLQUFiO0FBR0EsVUFBS04sZUFBTCxHQUF1QixNQUFLQSxlQUFMLENBQXFCTyxJQUFyQiw0RkFBdkI7QUFMaUI7QUFNbEI7Ozs7b0NBRWVDLEssRUFBTTtBQUNwQjtBQUNBLFdBQUtILEtBQUwsQ0FBV0wsZUFBWCxDQUEyQjtBQUFDUyxZQUFJLEVBQUVELEtBQUssQ0FBQ0UsYUFBTixDQUFvQkM7QUFBM0IsT0FBM0I7QUFFRDs7OzZCQUdRO0FBQ1BDLGFBQU8sQ0FBQ0MsR0FBUixDQUFZLEtBQUtSLEtBQWpCO0FBQ0EsMEJBQ0U7QUFBSyxpQkFBUyxFQUFDO0FBQWYsc0JBQ0ksOEZBREosZUFFSSxvRkFDRSxvRkFBSTtBQUFRLGVBQU8sRUFBRSxLQUFLTCxlQUF0QjtBQUF1QyxhQUFLLEVBQUM7QUFBN0MsdUJBQUosQ0FERixlQUVFLG9GQUFJO0FBQVEsZUFBTyxFQUFFLEtBQUtBLGVBQXRCO0FBQXVDLGFBQUssRUFBQztBQUE3QywrQkFBSixDQUZGLGVBR0Usb0ZBQUk7QUFBUSxlQUFPLEVBQUUsS0FBS0EsZUFBdEI7QUFBdUMsYUFBSyxFQUFDO0FBQTdDLDRDQUFKLENBSEYsZUFJRSxvRkFBSTtBQUFRLGVBQU8sRUFBRSxLQUFLQSxlQUF0QjtBQUF1QyxhQUFLLEVBQUM7QUFBN0MsMkRBQUosQ0FKRixDQUZKLGVBUUksc0ZBQWdCLEtBQUtLLEtBQUwsQ0FBV1IsUUFBWCxDQUFvQmlCLEdBQXBCLENBQXdCLFVBQUNDLE9BQUQsRUFBVUMsS0FBVjtBQUFBLDRCQUN0QztBQUFLLGFBQUcsRUFBRUE7QUFBVixXQUFrQkQsT0FBTyxDQUFDTixJQUExQixDQURzQztBQUFBLE9BQXhCLENBQWhCLENBUkosQ0FERjtBQWNEOzs7O0VBaEMwQlEsNENBQUssQ0FBQ0MsUyxHQW9DbkM7QUFDQTs7O0FBQ2VDLDBIQUFPLENBQUN4QixlQUFELEVBQWtCSSxpQkFBbEIsQ0FBUCxDQUE0Q0ssY0FBNUMsQ0FBZixFIiwiZmlsZSI6IjcuYnVuZGxlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiXHJcbid1c2Ugc3RyaWN0JztcclxuaW1wb3J0IFJlYWN0IGZyb20gXCJyZWFjdFwiO1xyXG5pbXBvcnQge1xyXG4gIEJyb3dzZXJSb3V0ZXIgYXMgUm91dGVyLFxyXG4gIFN3aXRjaCxcclxuICBSb3V0ZSxcclxuICBIYXNoUm91dGVyLFxyXG4gIExpbmtcclxufSBmcm9tIFwicmVhY3Qtcm91dGVyLWRvbVwiO1xyXG5cclxuaW1wb3J0IHsgY29ubmVjdCB9IGZyb20gJ3JlYWN0LXJlZHV4J1xyXG5pbXBvcnQgeyB1cGRhdGVQcm9kdWN0IH0gZnJvbSAgJy4uL2FjdGlvbnMvcHJvZHVjdHNBY3Rpb25zJztcclxuaW1wb3J0IHsgdXBkYXRlVXNlciB9IGZyb20gICcuLi9hY3Rpb25zL3VzZXJBY3Rpb25zJztcclxuXHJcbmNvbnN0IG1hcFN0YXRlVG9Qcm9wcyA9IHN0YXRlID0+ICh7XHJcbiAgcHJvZHVjdHM6IHN0YXRlLnByb2R1Y3RzLFxyXG4gIHVzZXI6IHN0YXRlLnVzZXJcclxufSk7XHJcblxyXG4vLyBUbyBhbGxvdyBhdXRvbWF0aWNhbGx5IGRpc3BhdGNoIFJlZHV4IEFjdGlvbiB0byB0aGUgUmVkdXggU3RvcmVcclxuY29uc3QgbWFwQWN0aW9uc1RvUHJvcHMgPSB7XHJcbiAgb25VcGRhdGVQcm9kdWN0IDogdXBkYXRlUHJvZHVjdCxcclxuICBvblVwZGF0ZVVzZXIgOiB1cGRhdGVVc2VyXHJcblxyXG59O1xyXG5cclxuY2xhc3MgSW50ZXJlc3RlZFBhZ2UgZXh0ZW5kcyBSZWFjdC5Db21wb25lbnQge1xyXG4gIGNvbnN0cnVjdG9yKHByb3BzKSB7XHJcbiAgICBzdXBlcihwcm9wcyk7XHJcbiAgICB0aGlzLnN0YXRlID0geyBsaWtlZDogZmFsc2UgfTtcclxuXHJcblxyXG4gICAgdGhpcy5vblVwZGF0ZVByb2R1Y3QgPSB0aGlzLm9uVXBkYXRlUHJvZHVjdC5iaW5kKHRoaXMpO1xyXG4gIH1cclxuXHJcbiAgb25VcGRhdGVQcm9kdWN0KGV2ZW50KXtcclxuICAgIC8vIFRoaXMgbWV0aG9kIGRlZmluZWQgaW4gbWFwQWN0aW9uc1RvUHJvcHMgb2JqZWN0XHJcbiAgICB0aGlzLnByb3BzLm9uVXBkYXRlUHJvZHVjdCh7bmFtZTogZXZlbnQuY3VycmVudFRhcmdldC52YWx1ZX0pO1xyXG5cclxuICB9XHJcblxyXG5cclxuICByZW5kZXIoKSB7XHJcbiAgICBjb25zb2xlLmxvZyh0aGlzLnByb3BzKTtcclxuICAgIHJldHVybihcclxuICAgICAgPGRpdiBjbGFzc05hbWU9XCJtYi0zXCI+XHJcbiAgICAgICAgICA8aDY+SSBhbSBpbnRlcmVzdGVkIGluOiA8L2g2PlxyXG4gICAgICAgICAgPHVsPlxyXG4gICAgICAgICAgICA8bGk+PGJ1dHRvbiBvbkNsaWNrPXt0aGlzLm9uVXBkYXRlUHJvZHVjdH0gdmFsdWU9XCJFLUxlYXJuaW5nXCI+RS1MZWFybmluZyA8L2J1dHRvbj48L2xpPlxyXG4gICAgICAgICAgICA8bGk+PGJ1dHRvbiBvbkNsaWNrPXt0aGlzLm9uVXBkYXRlUHJvZHVjdH0gdmFsdWU9XCJDbGFzc3Jvb20gdHJhaW5pbmdcIj5DbGFzc3Jvb20gdHJhaW5pbmcgPC9idXR0b24+PC9saT5cclxuICAgICAgICAgICAgPGxpPjxidXR0b24gb25DbGljaz17dGhpcy5vblVwZGF0ZVByb2R1Y3R9IHZhbHVlPVwiVmlydHVhbCBJbnN0cnVjdG9yIExlZCBUcmFpbmluZ1wiPlZpcnR1YWwgSW5zdHJ1Y3RvciBMZWQgVHJhaW5pbmcgPC9idXR0b24+PC9saT5cclxuICAgICAgICAgICAgPGxpPjxidXR0b24gb25DbGljaz17dGhpcy5vblVwZGF0ZVByb2R1Y3R9IHZhbHVlPVwiT3RoZXJcIj5PdGhlciAoZXhhbXBsZSBwb2RjYXN0LCBpbmZvZ3JhcGhpY3MsIHBvc3RlcnMpIDwvYnV0dG9uPjwvbGk+XHJcbiAgICAgICAgICA8L3VsPlxyXG4gICAgICAgICAgPGRpdj5TZWxlY3RlZDoge3RoaXMucHJvcHMucHJvZHVjdHMubWFwKChwcm9kdWN0LCBpbmRleCkgPT4gKFxyXG4gICAgICAgICAgICA8ZGl2IGtleT17aW5kZXh9Pntwcm9kdWN0Lm5hbWV9PC9kaXY+XHJcbiAgICAgICAgICAgICkpfTwvZGl2PlxyXG4gICAgICA8L2Rpdj5cclxuICAgICk7XHJcbiAgfVxyXG59XHJcblxyXG5cclxuLy8gd3JhcCBJbnRlcmVzdGVkUGFnZSBpbiBjb25uZWN0IGFuZCBwYXNzIGluIG1hcFN0YXRlVG9Qcm9wc1xyXG4vLyBleHBvcnQgZGVmYXVsdCBjb25uZWN0KG1hcFN0YXRlVG9Qcm9wcykoSW50ZXJlc3RlZFBhZ2UpXHJcbmV4cG9ydCBkZWZhdWx0IGNvbm5lY3QobWFwU3RhdGVUb1Byb3BzLCBtYXBBY3Rpb25zVG9Qcm9wcykoSW50ZXJlc3RlZFBhZ2UpO1xyXG4iXSwic291cmNlUm9vdCI6IiJ9