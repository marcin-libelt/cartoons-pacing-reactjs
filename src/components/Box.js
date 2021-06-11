import React, {memo, useState} from 'react';
import { useDrag } from 'react-dnd';
import { qtyReducer } from '../helper';
const style = {
    border: '1px dashed gray',
    backgroundColor: 'white',
    marginBottom: '0.5rem',
    cursor: 'move'
};

const labelStyle = {
    fontSize: '10px',
    paddingRight: '10px',
    color: '#757575'
}

export const Box = memo(function Box({ name, hidden, type, id, doorLabel, PO, doorCode, sku, sizes, clientName,
                                         joorSONumber,
                                         orderType,
                                         unit_selling_price,
                                         warehouseLocation, isDropped }) {

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


    return qtyReducer(sizes) > 0 ? <div hidden={hidden} className={'card'} ref={drag} role="Box" style={{ ...style, opacity, backgroundColor, borderRadius: '10px', overflow: 'hidden' }}>
        <div className={'m-0 px-2 py-1'} style={{backgroundColor: '#c5c5c5'}}>

            <div className={'d-flex'} style={{fontSize: '12px'}}>
                <p style={{width: '40%', lineHeight: '1.5'}} className={'m-0'}>
                    <strong style={{lineHeight: '2'}}>{name}</strong><span style={{fontSize: '13px', fontStyle: 'italic', marginLeft: '8px'}}>{sku}</span><br/>
                    <span className={'label'} style={labelStyle}>PO number:</span>{PO}<br/>
                    <span className={'label'} style={labelStyle}>SO number:</span>{joorSONumber}<br/>
                </p>
                <p style={{ width: '60%', lineHeight: '1.5'}} className={'m-0'}>
                    <span className={'label'} style={labelStyle}>door label:</span>{doorLabel}<br/>
                    <span className={'label'} style={labelStyle}>client name:</span>{clientName}
                    &nbsp;/&nbsp;<strong style={{color: warehouseLocation === 'VAT' ? '#8e6009' : '#1b1fb9'}}>{warehouseLocation}</strong><br/>
                    <span className={'label'} style={labelStyle}>order type:</span>{orderType}<br/>
                    <span className={'label'} style={labelStyle}>unit price:</span>{parseFloat(unit_selling_price) + ' €'}
                </p>
            </div>
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

export const BoxAfter = memo(function BoxAfter({ name, type, id, doorLabel, cartonBox, sku, sizes, handleSetQty, handleRemoveItemFromDustbin }) {
    const backgroundColor = '#e9ecef';
    return (<div className={'card'} role="Box" style={{ ...style, backgroundColor, borderRadius: '10px', overflow: 'hidden' }}>
        <div className={'m-0 px-2 py-1'} style={{backgroundColor: '#c5c5c5'}}>
            <p className={'m-0 mb-2'}>
                <strong>{name}</strong><span style={{fontSize: '13px', fontStyle: 'italic', marginLeft: '8px'}}>{sku}</span>
                <button type={'button'} className={'btn btn-secondary btn-sm'}
                        style={{float: 'right',fontSize: '12px', paddingTop: '1px',
                            paddingBottom: '1px', marginTop: '2px'}}
                        onClick={(e) => { return  handleRemoveItemFromDustbin(id, cartonBox, e); }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor"
                         className="bi bi-x-lg" style={{ marginBottom: '-1px', marginRight: '5px'}} viewBox="0 0 16 16">
                        <path
                            d="M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z"/>
                    </svg> remove item</button>
            </p>
        </div>
        <div className={'card-body p-3'}>
            <form onSubmit={ev => { ev.preventDefault()}}>
                <div className={''}>
                    {sizes.map(({qty, size, barcode}, index) => <div key={index} className="size-input">
                        <div className="">
                            {size}
                        </div>
                        <input type="number"
                                   value={qty}
                                   onChange={(e) => handleSetQty(e, id, cartonBox, barcode)}
                                   className=""/>
                    </div>)}
                </div>
            </form>
        </div>
    </div>);
});
