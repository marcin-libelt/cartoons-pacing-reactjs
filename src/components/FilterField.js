import React from 'react';

export default function ({onFilter, value, label,  isFirst = false}) {
    return  <div className="col-6 input-group mb-1" style={{marginLeft: !isFirst ? '5px' : '0'}}>
        <div className="input-group-prepend">
            <span className="input-group-text">{label}</span>
        </div>
        <input type="text"
               className={'form-control form-control-sm'}
               onChange={onFilter} value={value}>
        </input>
    </div>
}