import { UPDATE_PRODUCT } from '../actions/productsActions';

export default function productsReducer(state = [], {type, payload}){
	// if(action.type === 'changeState'){
	// 	return action.payload.newState;
	// }

	switch(type){
		case UPDATE_PRODUCT:
			return payload.products;
			break;
		default:
			return state;
	}
}
