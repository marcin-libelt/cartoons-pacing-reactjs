import swal from 'sweetalert';

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
            errors.push(key)
        }
    })
    if(errors.length > 0) {
        swal("Ops...", "You can not add item with different " + errors.join(" and ") + ".", "error");
        return false;
    }
    return true;
}

export { qtyReducer, validateCartonInput };

