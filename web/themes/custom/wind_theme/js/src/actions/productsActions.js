export const UPDATE_PRODUCT = 'products:updateProduct';

export function updateProduct(product){

	return {
		type: UPDATE_PRODUCT,
		payload: {
			products: [product]
		}
	}
}