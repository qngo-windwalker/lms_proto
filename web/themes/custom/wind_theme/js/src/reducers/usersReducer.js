import { UPDATE_USER } from '../actions/userActions';

export default function userReducer(state = '', {type, payload}){
	console.log(type);
	console.log(UPDATE_USER);
	switch(type){
		case UPDATE_USER:
			console.log('update_user: ' + payload.user);
			return payload.user;
			break;
		default:
			return state;
	}
}
