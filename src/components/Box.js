import React, {memo, useState} from 'react';
import { useDrag } from 'react-dnd';
const style = {
    border: '1px dashed gray',
    backgroundColor: 'white',
    marginBottom: '0.5rem',
    cursor: 'move'
};

function qtyReducer(accu, curr, i)  {
    return i > 1 ? accu + curr.qty : accu.qty + curr.qty
}

export const Box = memo(function Box({ name, type, id, doorLabel, PO, doorCode, sku, sizes, isDropped }) {

    const [{ opacity }, drag] = useDrag(() => ({
        type,
        item: { id, doorLabel, doorCode },
        canDrag: (monitor) => {
            return true //qty > 0;
        },
        collect: (monitor) => ({
            opacity: monitor.isDragging() ? 0.4 : 1,
        }),
    }), [type]);

    const backgroundColor = '#e9ecef'; // qty > 0 ? '' : '#e9ecef'

    return sizes.reduce(qtyReducer) > 0 ? <div className={'card'} ref={drag} role="Box" style={{ ...style, opacity, backgroundColor, borderRadius: '10px', overflow: 'hidden' }}>
        <div className={'m-0 px-2 py-1'} style={{backgroundColor: '#c5c5c5'}}>
            <p className={'m-0 mb-2'}>
                <strong>{name}</strong>
                <span style={{float: 'right', fontSize: '12px',lineHeight: '2'}}>{PO}</span>
            </p>
            <p className={'m-0'}>
                <span style={{}}><span>{sku}</span></span>
                <span style={{float: 'right',fontSize: '12px', lineHeight: '2'}}>{doorLabel}</span>
            </p>
        </div>
        <div className={'card-body p-3'}>
            <div className={'d-flex'}>
                {sizes.map(({qty, size}, index) => {
                    const opacity = !qty ? 0.2 : 1;
                    return <div key={index} className="" style={{opacity, flexBasis: '100%'}}>
                        <div style={{ textAlign: 'center', borderBottom: '1px solid #ccc'}}>
                            {size}
                        </div>
                        <div style={{ textAlign: 'center', fontSize: '13px'}}>{qty}</div>
                    </div>
                })}
            </div>
        </div>
    </div> : '';
});

export const BoxAfter = memo(function BoxAfter({ name, type, id, doorLabel, PO, cartonBox, sku, sizes, handleSetQty, handleRemoveItemFromDustbin }) {
    const backgroundColor = '#e9ecef';
    return (<div className={'card'} role="Box" style={{ ...style, backgroundColor, borderRadius: '10px', overflow: 'hidden' }}>
        <div className={'m-0 px-2 py-1'} style={{backgroundColor: '#c5c5c5'}}>
            <p className={'m-0 mb-2'}>
                <strong>{name}</strong>
                <span style={{float: 'right', fontSize: '12px',lineHeight: '2'}}>{PO}</span>
            </p>
            <p className={'m-0'}>
                <span style={{}}><span>{sku}</span></span>
                <button type={'button'} className={'btn btn-secondary btn-sm'}
                        style={{float: 'right',fontSize: '12px', paddingTop: '1px',
                            paddingBottom: '1px'}}
                        onClick={(e) => { return  handleRemoveItemFromDustbin(id, cartonBox, e); }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor"
                         className="bi bi-x-lg" style={{ marginBottom: '-1px', marginRight: '5px'}} viewBox="0 0 16 16">
                        <path
                            d="M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z"/>
                    </svg> remove item</button>
            </p>
        </div>
        <div className={'card-body p-3'}>

            <div className={''}>
                {sizes.map(({qty, size, barcode}, index) => <div key={index} className="size-input">
                    <div className="">
                        {size}
                    </div>
                    <input type="number"
                               value={qty}
                               onChange={(e) => handleSetQty(parseInt(e.target.value), id, cartonBox, barcode)}
                               className=""/>
                </div>)}
            </div>
        </div>
    </div>);
});
