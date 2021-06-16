import React, {useState, useCallback, memo, useEffect} from 'react';
import { Dustbin } from '../components/Dustbin';
import { Box } from '../components/Box';
import { ItemTypes, FieldsMap } from '../ItemTypes';
import uuid from 'react-uuid'
import update from 'immutability-helper';
import { qtyReducer, validateCartonInput } from '../helper';
import swal from "sweetalert";

export const Container = memo(function Container(props) {

    const { data } = props;

    // for now only 1 Type ( style )
    const [dustbins, setDustbins] = useState([]);
    const [pickedItems, setPickedItems] = useState([]);
    const [boxes, setBoxes] = useState([]);
    const [filter, setFilter] = useState("");
    const [filterSo, setFilterSo] = useState("");
    const [loadingMsg, setLoadingMsg] = useState("");
    const [afterSubmition, setAfterSubmition] = useState(false);
    const [cartonOptions, setCartonOptions] = useState([]);
    const [invAmount, setInvAmount] = useState("");
    const [invNumber, setInvNumber] = useState("")

    const [totals, setTotals] = useState({
        cartons: 0,
        units: 0,
        value: 0
    })

    useEffect(function () {
        setBoxes(data.data.orders); // props.data
        setCartonOptions(data.data.cartons);
        handleNewDustbin();
    }, []);

    useEffect(() => {
        const updatedState = update(totals, {
            ['cartons']: { $set: dustbins.length }
        })
        setTotals(updatedState);
    }, [dustbins])

    useEffect(() => {

        let qty = 0;
        let value = 0;

        pickedItems.forEach(item => {
            const thisQty = parseInt(qtyReducer(item.sizes));
            qty += thisQty;
            value += thisQty * item.unit_selling_price;
        })

        const updatedState = update(totals, {
            ['units']: { $set: qty },
            ['value']: { $set: parseFloat(value)}
        })

        setTotals(updatedState);
    }, [pickedItems])

    /**
     *
     * @type {(function(*, *): void)|*}
     */
    const handleDrop = useCallback((cartonBox, { id, doorCode }) => {
        const result = boxes.find(item => item.id === id);
        const index = boxes.indexOf(result);
        const totalQty = qtyReducer(result.sizes);

        // quit if no items to distribute left
        if(totalQty === 0) {
            return; // Abort!
        }

        // sprzwdz czy karton ma item z tym samym doorCodem
        // jednakowe pola w kartonie:
        // -- doorCode,
        // -- joorSONumber,
        // -- orderType
        // -- PO nummber
        //
        const targetDustbin = dustbins.find(bin => bin.uid === cartonBox);
        if(!validateCartonInput(targetDustbin, {
            doorCode: result.doorCode,
            orderType: result.orderType,
            joorSONumber: result.joorSONumber,
            PO: result.PO
        })) {
            return; // Abort!
        }


        // jeśli nie ma doorcode, oznacza ze karton jest pusty
        if(targetDustbin.isEmpty) {
            // ustaw doorCode dla kartonu
            const index = dustbins.indexOf(targetDustbin);
            const updatedDustbin = update(dustbins, {
                [index]: {
                    isEmpty: {$set: false},
                    orderType: {$set: result.orderType},
                    joorSONumber: {$set: result.joorSONumber},
                    doorCode: {$set: result.doorCode},
                    PO: {$set: result.PO},
                    toDoorLabel: {$set: result.doorLabel}
                }
            });
            setDustbins(updatedDustbin)
        }

        // update -------- Right
        // check if given ID exists in group
        // sprawdz czy Produkt znajduje się w tym kartonie
        const result2 = pickedItems.find(item => item.id === id && item.cartonBox === cartonBox);

        if(!result2) {
            // nie ma produktu o takim ID w kartonie
            // więc dodaj normalnym trybem
            // zeruj całośc z palety i przypisz wartości qty do kartonu
            const copy = result.sizes.map((a,i) => {
                return {
                    qty: result.sizes[i].qty,
                    size: result.sizes[i].size,
                    barcode: result.sizes[i].barcode,
                };
            })
            const newTarget = {
                cartonBox,
                id,
                sku: result.sku,
                PO: result.PO,
                name: result.name,
                sizes: copy,
                clientName: result.clientName,
                joorSONumber: result.joorSONumber,
                orderType: result.orderType,
                unit_selling_price: result.unit_selling_price,
                warehouseLocation: result.warehouseLocation
            }
            setPickedItems(prevState => [...prevState, newTarget])

        } else {
            // w kartonie jest juz ten produkt o taki ID
            // dopisz więc pozostała ilość rozmiarów
            // pozostałe rozmiary po lewej na palecie
            const isAbandonedSizes = result.sizes.filter(s => s.qty > 0);
            let dupa = [...result2.sizes];
            const result2Index = pickedItems.indexOf(result2);
            result2.sizes.forEach(item => {
                const {barcode} = item;
                const abandoned = isAbandonedSizes.find(item => item.barcode === barcode)
                if(abandoned) {
                    const thisItem = result2.sizes.find(s => s.barcode === barcode);
                    const index = result2.sizes.indexOf(thisItem);

                    dupa = update(dupa, {
                        [index]: {
                            qty: { $set: thisItem.qty + abandoned.qty}
                        }
                    })
                }
            })
            // w kartonie
            const newTarget = update(pickedItems, {
                [result2Index]: {
                    sizes: { $set: dupa}
                }
            });
            setPickedItems(newTarget);
        }

        // Zamień na zera - produkt przeniesiony z palety
        // za każdym razem gdy przenosimy z Lewej
        const zero = result.sizes.map((a,i) => {
            return {
                qty: 0,
                size: result.sizes[i].size,
                barcode: result.sizes[i].barcode
            };
        })
        const updateSource = {
            id: result.id,
            name: result.name,
            doorLabel: result.doorLabel,
            doorCode: result.doorCode,
            PO: result.PO,
            sku: result.sku,
            sizes: zero,
            type: result.type,
            clientName: result.clientName,
            joorSONumber: result.joorSONumber,
            orderType: result.orderType,
            unit_selling_price: result.unit_selling_price,
            warehouseLocation: result.warehouseLocation
        }
        setBoxes(prevState => {
            prevState[index] = updateSource;
            return [...prevState]
        });

    }, [boxes, pickedItems, dustbins]);

    function handleNewDustbin() {
        const newDustbin = {
            uid: uuid(),
            accepts: [ItemTypes.STYLE],
            doorCode: null,  // unique for dustbin
            orderType: null, // unique for dustbin
            joorSONumber: null, // unique for dustbin
            PO: null, // unique for dustbin
            toDoorLabel: null,
            gross_weight: '',
            net_weight: '',
            dimensions: '',
            suffix: '',
            isEmpty: true
        };
        setDustbins(prevState => {
            return [...prevState, newDustbin];
        })
    }

    /**
     * Usuwa item z kartonu
     * przywraca po kolei rozmiary w odpowiedniej ilosci na paletę
     * oraz usuwa rekord zapakowanego produktu z kartonu
     * @param id
     * @param cartonBox
     */
    function removeItemFromDustbin(id, cartonBox, ev) {
        ev.preventDefault();

        const newState = prepareRemoveItemFromDustbin(id, cartonBox)
        if(!newState) {
            return;
        }

        // sprawdz czy był ostatni, i on gasi światło
        const countBefore = pickedItems.filter(item => item.cartonBox === cartonBox).length;
        if(countBefore === 1) {
            const dustbin = dustbins.find(bin => bin.uid === cartonBox);
            const index = dustbins.indexOf(dustbin);

            setDustbins(update(dustbins, {
                [index]: {
                    isEmpty: { $set: true},
                    doorCode: { $set: null},
                    toDoorLabel: { $set: null},
                    orderType: { $set: null},
                    joorSONumber: { $set: null},
                    PO: { $set: null}
                }
            }))
        }

        const item = pickedItems.find(item => item.id === id);
        const index = pickedItems.indexOf(item);
        const newPickedItems = update(pickedItems, {
            $splice: [[index, 1]]
        });

        setPickedItems(newPickedItems);
        setBoxes(newState.boxes);
    }

    function prepareRemoveItemFromDustbin(id, cartonBox, cummulativeArray = {}) {

        if(!cummulativeArray.boxes) {
            cummulativeArray.boxes = boxes;
            cummulativeArray.pickedItems = pickedItems;
        }

        const item = pickedItems.find(item => item.id === id && item.cartonBox === cartonBox);
        const positiveSizes = item.sizes.filter(size => size.qty > 0);

       // let data = {}
        positiveSizes.forEach(size => {
            cummulativeArray = setQty(0, id, cartonBox, size.barcode, cummulativeArray);
        })

        return cummulativeArray;
    }

    function handleRemoveDustbin(cartonBox) {

        const results = pickedItems.filter(item => item.cartonBox === cartonBox);
        if (results.length > 0) {
            if (!window.confirm('Carton isn\'t empty. Still want to remove it?')) {
                return;
            }

            let data = {};
            results.forEach(({ id }) => {
                data = prepareRemoveItemFromDustbin(id, cartonBox, data);
            })

            setBoxes(data.boxes);

           // usuń ------------- z pickedItems
            setPickedItems(prevState => {
                return prevState.filter(item => item.cartonBox !== cartonBox);
            })
        }

        const updatedDustbins = update(dustbins, {}).filter(bin => bin.uid !== cartonBox)

        // usuń --------------- karton
        setDustbins(updatedDustbins)

        const updatedState = update(totals, {
            ['cartons']: { $set: updatedDustbins.length }
        })
        setTotals(updatedState);
    }

    /**
     * Size quantity handler
     * @param value
     * @param id
     * @param cartonBox
     * @param barcode
     */
    function handleSetQty(ev, id, cartonBox, barcode) {
        const value = ev.target.value;
        if(value === "") {
            return
        }

        const newState = setQty(value, id, cartonBox, barcode);
        if(!newState) {
            return;
        }
        setBoxes(newState.boxes);
        setPickedItems(newState.pickedItems);
    }

    /**
     * Recursive function, called on each action:
     * setQty for 1 size, drag item to carton box, removing carton box, removing item from carton box
     *
     * @param value
     * @param id
     * @param cartonBox
     * @param barcode
     * @param resursciveArray
     * @returns {{}} updated data ready to state update for 'boxes' and 'pickedItems'
     */
    function setQty(value, id, cartonBox, barcode, cummulativeArray = {}) {

        if(!cummulativeArray.boxes) {
            cummulativeArray.boxes = boxes;
            cummulativeArray.pickedItems = pickedItems;
        }

        const thatItem = boxes.find(item => item.id === id);
        const thatItemIndex = boxes.indexOf(thatItem);

                const itemSize = thatItem.sizes.find(size => size.barcode === barcode);
                const itemSizeIndex = thatItem.sizes.indexOf(itemSize);

        const thatRightItem = pickedItems.find(item => item.id === id && item.cartonBox === cartonBox)
        const thatRightIndex = pickedItems.indexOf(thatRightItem);

                const rightSize = thatRightItem.sizes.find(size => size.barcode === barcode);
                const rightSizeIndex = thatRightItem.sizes.indexOf(rightSize);

        // w tym momencie mamy
        // jeśli po lewej jest pusto - to przerwij akcje

        if((value - (rightSize.qty)) > (itemSize.qty) || value < 0 ) {
            return;
        }

        const qty1 = parseInt(itemSize.qty - (value - (rightSize.qty)));
        const qty2 = parseInt(value);


        // na palecie
        cummulativeArray.boxes = update(cummulativeArray.boxes, {
            [thatItemIndex]: {
                sizes: {[itemSizeIndex]: { qty: {$set: qty1}}}
            }
        });

        // w kartonie
        cummulativeArray.pickedItems = update(cummulativeArray.pickedItems, {
            [thatRightIndex]: {
                sizes: {[rightSizeIndex]: { qty: {$set: qty2}}}
            }
        });
        return cummulativeArray
    }

    function submitPacking() {

        if(pickedItems.length === 0) {
            swal('There\'s no cartons to submit new ASN.', {
                icon: "warning"
            })
            return;
        }

        if(!invAmount || !invNumber) {
            swal('\'Invoice Number\' and \'Invoice Amount\' fields can not be empty.', {
                icon: "warning"
            })
            return;
        }

        setLoadingMsg("trwa wysyłanie");

        let resultObject = {
            cartons: [],
            invoice_amount: invAmount,
            invoice_number: invNumber,
            factory_id: data.factory_id,
            form_key: data.form_key
        };

        dustbins.forEach( ({ uid, doorCode, gross_weight, net_weight, dimensions, suffix, joorSONumber, PO },index) => {

            const allpackedItemInDustbin = pickedItems.filter(item => item.cartonBox === uid);
            if(allpackedItemInDustbin) {
                const itemsCollection = [];
                allpackedItemInDustbin.forEach(({ PO, sizes, sku}) => {
                    itemsCollection.push({
                        PO,
                        sizes,
                        sku
                    });
                })

                const binData = {
                    cartonId: uid,
                    doorCode,
                    gross_weight,
                    net_weight,
                    dimensions,
                    suffix,
                    joorSONumber,
                    PO,
                    items: itemsCollection
                }
                resultObject.cartons.push(binData)
            }
        })


        swal({
            title: "Are you sure?",
            text: "You are about to submit new ASN.\nDo you want to proceed?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willProceed) => {
                if (willProceed) {

                    data.jquery.ajax({
                        type: "POST",
                        url: data.post_url,
                        data: resultObject,
                        dataType: 'json'
                    })
                        .done(function (response, status) {
                            const finalMsg = response.message + '\n ';
                            var redirectUrl = response.redirect_url;

                            swal(response.message, {
                                icon: status,
                            });
                            setTimeout(() => {
                                window.location.href = redirectUrl;
                            }, 1500);
                        })
                        .error(function(response, status){
                            swal(response.responseJSON.message, {
                                icon: status
                            });
                        })
                        .always(function (){
                            setLoadingMsg("")
                        });

                } else {
                    setLoadingMsg("")
                }
            });



    }

    function handleSetCartonInfo(value, field, uid) {
        const dustbin = dustbins.find(bin => bin.uid === uid)
        const index = dustbins.indexOf(dustbin);
        const newState = update(dustbins, {
            [index]: {
                [field]: { $set: value }
            }
        })
        setDustbins(newState)
    }

    function filterBy(ev, field = '') {
        if(field === 'so') {
            setFilterSo(ev.target.value);
        } else {
            setFilter(ev.target.value);
        }
    }

    return <div className="container" style={{ position: 'relative', color: '#212529', fontSize: '15px'}}>
        <div className={'row'}>
            <div className={'col d-flex justify-content-between top-panel'}>
                <div className="card filters">
                    <h4>Filter by:</h4>
                    <form onSubmit={ev => { ev.preventDefault()}}>
                        <div className="input-group mb-3">
                            <div className="input-group-prepend">
                                <span className="input-group-text" id="inputGroup-sizing-default">PO number or SKU</span>
                            </div>
                            <input type="text" className={'form-control'} onChange={(ev) => filterBy(ev)} value={filter}  aria-label="Sizing example input"
                                   aria-describedby="inputGroup-sizing-default">
                            </input>
                        </div>
                        <div className="input-group mb-3">
                            <div className="input-group-prepend">
                                <span className="input-group-text" id="inputGroup-sizing-default">SO number</span>
                            </div>
                            <input type="text" className={'form-control'} onChange={(ev) => filterBy(ev, 'so')} value={filterSo}  aria-label="Sizing example input"
                                   aria-describedby="inputGroup-sizing-default">
                            </input>
                        </div>
                    </form>
                </div>
                <div className="card totals">
                    <h4>Totals</h4>
                    <span className='label'>cartons:</span>{totals.cartons}<br/>
                    <span className='label'>units:</span>{totals.units} pcs<br/>
                    <span className='label'>value:</span>{totals.value} &euro;<br/>
            </div>
            </div>
        </div>
        <div className="row">
                <div className="col col-5">
                    <div className={'d-flex flex-column'}>

                        { boxes.length > 0 ? boxes.map((box, index) => {

                            const { name,
                                   type,
                                   sku,
                                   PO,
                                   doorCode,
                                   doorLabel,
                                   id,
                                   sizes,
                                   clientName,
                                   joorSONumber,
                                   orderType,
                                   unit_selling_price,
                                   warehouseLocation } = box;

                            const isVisible = (
                                    box.PO.includes(filter)
                                    || box.sku.includes(filter)
                                )
                                && box.joorSONumber.includes(filterSo)

                            return (<Box name={name}
                                 hidden={!isVisible}
                                 type={type}
                                 sku={sku}
                                 doorLabel={doorLabel}
                                 doorCode={doorCode}
                                 id={id}
                                 PO={PO}
                                 sizes={sizes}
                                 clientName={clientName}
                                 joorSONumber={joorSONumber}
                                 orderType={orderType}
                                 unit_selling_price={unit_selling_price}
                                 warehouseLocation={warehouseLocation}
                                 boxAfter={false}
                                 key={index}/>)}) : <p>There is no items left for search criteria: "{filter}".</p> }
                    </div>
                </div>
                <div className="col col-7">
                    <div className={'new-carton2'}>
                        <button type="button" onClick={handleNewDustbin} className="btn btn-primary btn-sm">
                            +&nbsp;&nbsp;<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                              className="bi bi-box-seam" viewBox="0 0 16 16">
                            <path
                                d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                        </svg> add new carton</button>
                    </div>
                    <div className={'mb-3'} style={{ height: '100%'}}>
                        {dustbins.map(({
                                           accepts,
                                           uid,
                                           toDoorLabel,
                                           orderType,
                                           joorSONumber,
                                            PO,
                                           gross_weight,
                                            net_weight,
                                            dimensions,
                                            suffix}, index) => {
                            const info = {
                                gross_weight,
                                net_weight,
                                dimensions,
                                suffix
                            }
                            return <Dustbin accept={accepts}
                                            onDrop={(item) => handleDrop(uid, item)}
                                            isMoreDustbins={dustbins.length > 1}
                                            handleRemoveDustbin={handleRemoveDustbin}
                                            handleRemoveItemFromDustbin={removeItemFromDustbin}
                                            handleSetQty={handleSetQty}
                                            uid={uid}
                                            toDoorLabel={toDoorLabel}
                                            orderType={orderType}
                                            PO={PO}
                                            joorSONumber={joorSONumber}
                                            setCartonInfo={handleSetCartonInfo}
                                            readOnly={false}
                                            info={info}
                                            cartonOptions={cartonOptions}
                                            assignedItems={pickedItems && pickedItems.filter(item => item.cartonBox === uid)}
                                            index={index}
                                            key={index}/>
                        })}
                    </div>
                </div>
            </div>
        <div className={'row'}>
            <div className="col footer">
                <form onSubmit={ev => { ev.preventDefault()}}>
                    <div className="input-group mb-3">
                        <div className="input-group-prepend">
                            <span className="input-group-text">Invoice Number</span>
                        </div>
                        <input type="text" className={'form-control'} onChange={ev => {setInvNumber(ev.target.value)}} value={invNumber}></input>
                    </div>
                    <div className="input-group mb-3">
                        <div className="input-group-prepend">
                            <span className="input-group-text">Invoice Amount</span>
                        </div>
                        <input type="text" className={'form-control'} onChange={ev => {setInvAmount(ev.target.value)}} value={invAmount}></input>
                    </div>
                </form>
                <div style={{ textAlign: 'center'}}>
                    <button type="button" style={{minWidth: '250px'}} onClick={ submitPacking } className="btn primary btn-lg">Create ASN</button>
                </div>
            </div>
        </div>
        { loadingMsg ? <span className={'mask'} style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: '#fff', opacity: 0.4 }}>
            <span>{loadingMsg}</span>
        </span> : ''}
        </div>
});

