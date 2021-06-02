
const reducer = (accu, curr, i) => {
    return i > 1 ? accu + curr.qty : accu.qty + curr.qty
}

const qtyReducer = (sizes = []) => {
    if(sizes.length === 0) {
        return 0
    }
    if (sizes.length === 1) {
        return sizes[0].qty;
    } else {
        return sizes.reduce(reducer)
    }
}

const validateCartonInput = (dustbin, result) => {
    let errors = [];
    Object.keys(result).forEach(key => {
        if(!!dustbin[key] && dustbin[key] !== result[key]) {
            errors.push(key + ' isn\'t same! Aborting')
        }
    })
    if(errors.length > 0) {
        console.log(errors);
        return false;
    }
    return true;
}

export { qtyReducer, validateCartonInput };

