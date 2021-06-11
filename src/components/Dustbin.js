import React, {memo, useState } from 'react';
import {useDrop} from 'react-dnd';
import {BoxAfter} from './Box';

export const Dustbin = memo(function Dustbin({
                                                 accept,
                                                 onDrop,
                                                 assignedItems,
                                                 handleSetQty,
                                                 handleRemoveItemFromDustbin,
                                                 isMoreDustbins,
                                                 uid,
                                                 info,
                                                 toDoorLabel,
                                                 orderType,
                                                 PO,
                                                 joorSONumber,
                                                 handleRemoveDustbin,
                                                 setCartonInfo,
                                                 index,
                                                 cartonOptions
                                             }) {

    const [{isOver, canDrop,}, drop] = useDrop({
        accept: accept,
        drop: onDrop,
        collect: (monitor) => ({
            isOver: monitor.isOver(),
            canDrop: monitor.canDrop()
        }),
    });

    const [isOpen2, setIsOpen2] = useState(true);
    const isActive = isOver && canDrop;
    let backgroundColor = '#dcd0b9';
    if (isActive) {
        backgroundColor = '#ead09d';
    } else if (canDrop) {
        backgroundColor = '#dcd0b9';
    }

    const styles = {
        borderWidth: '0 0 1px',
        backgroundColor: '#d4c7af',
        width: '100%'
    }

    const styles2 = {
        paddingTop: '0.5rem',
        paddingBottom: '0.5rem',
    }

    const dustbinStyles = {
        borderRadius: '10px',
        overflow: 'hidden',
        backgroundColor: backgroundColor
    }

    return (<div className={'card sticky-card mb-2'} ref={drop} role="Dustbin" style={{...dustbinStyles}}>
        <p className={'m-0 cartonHead'}>
            <span className={'label'}>CartonBox</span>

            {toDoorLabel ? <>
                <span title={'PO number'}>{PO}</span>&nbsp;/&nbsp;
                <span title={'Door Label'}>{toDoorLabel}</span>&nbsp;/&nbsp;
                <span title={'Order Type'}>{orderType}</span>&nbsp;/&nbsp;
                <span title={'SO number'}>{joorSONumber}</span>
                </> : <span style={{color: '#463f31', fontStyle: 'italic'}}>{'empty'}</span>}

            <span className={'dustbin-toggler'} onClick={() => { setIsOpen2(!isOpen2) }}>
                { isOpen2 ? <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                 className="bi bi-caret-down-fill" viewBox="0 0 16 16">
                    <path
                        d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                </svg> : <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                 className="bi bi-caret-up-fill" viewBox="0 0 16 16">
                    <path
                        d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>
                </svg> }
            </span>
        </p>
        <div className={'card-body'} style={ !isOpen2 ? styles2 : {}}>

            { isOpen2 ? <React.Fragment><div className={'row mb-4'} style={{ marginLeft: '-10px', marginRight: '-10px'}}>
                    <div className={'col col-xs-2 mb-1'} style={{ padding: '0 10px', flex: 'auto'}}>
                        <label style={{fontSize: '13px', minWidth: '100px'}}>Gross weight</label>
                        <input type="text" style={styles} value={info.gross_weight}
                               onChange={(e) => setCartonInfo(e.target.value, 'gross_weight', uid)}/><br/>
                    </div>
                    <div className={'col col-xs-2 mb-1'} style={{ padding: '0 10px', flex: 'auto'}}>
                        <label style={{fontSize: '13px', minWidth: '100px'}}>Net weight</label>
                        <input type="text" style={styles} value={info.net_weight}
                               onChange={(e) => setCartonInfo(e.target.value, 'net_weight', uid)} placeholder={''}/><br/>
                    </div>
                    <div className={'col col-xs-6 mb-1'} style={{ padding: '0 10px', flex: 'auto'}}>
                        <label style={{fontSize: '13px', minWidth: '100px'}}>Dimensions</label>
                        <select className="form-control"
                                onChange={(e) => setCartonInfo(e.target.value, 'dimensions', uid)}
                                style={styles} value={info.dimensions}>
                            <option key={'select'}>{'-- select --'}</option>
                            { cartonOptions.map((opt, index) => <option key={index}>{opt}</option>)}
                        </select>
                    </div>
                    <div className={'col col-xs-2 mb-1'} style={{ padding: '0 10px', flex: 'auto'}}>
                        <label style={{fontSize: '13px', minWidth: '100px'}}>Suffix</label>
                        <input type="text" style={styles} value={info.suffix}
                               onChange={(e) => setCartonInfo(e.target.value, 'suffix', uid)} placeholder={''}/><br/>
                    </div>
                </div>
                <div>
                    {assignedItems && assignedItems.length > 0 ? assignedItems.map(({
                                                                                        name,
                                                                                        type,
                                                                                        sku,
                                                                                        doorLabel,
                                                                                        PO,
                                                                                        id,
                                                                                        sizes,
                                                                                        cartonBox
                                                                                    }, index) => (
                            <BoxAfter name={name}
                                      type={type}
                                      sku={sku}
                                      doorLabel={doorLabel}
                                      id={id}
                                      cartonBox={cartonBox}
                                      sizes={sizes}
                                      boxAfter={true}
                                      handleRemoveItemFromDustbin={handleRemoveItemFromDustbin}
                                      handleSetQty={handleSetQty}
                                      isDropped={false} key={index}/>))
                        : <div className={'mb-2'}
                               style={{border: '1px dashed', borderRadius: '5px', textAlign: 'center', padding: '5px'}}>
                            This is an empty carton box<br/>Drag Items and Drop it here</div>}
                </div>
                <p style={{fontSize: '10px', float: 'right', color: '#826d46', lineHeight: 3.4 }} className={'m-0'}>{uid}</p>
                <button type="button" className="btn btn-danger btn-sm" onClick={() => handleRemoveDustbin(uid)}>
                    &nbsp;
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         className="bi bi-trash-fill" viewBox="0 0 16 16">
                        <path
                            d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                    </svg>
                    &nbsp;&nbsp;{'remove carton'}</button>
            </React.Fragment> : ''}
        </div>
    </div>)
});
