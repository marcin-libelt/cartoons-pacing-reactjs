import React, {useState, useCallback, memo, useEffect, useRef} from 'react';
import { Dustbin } from '../components/Dustbin';
import { Box } from '../components/Box';
import FilterField from '../components/FilterField';
import DocumentStatus from "../components/DocumentStatus";
import { ItemTypes, FieldsMap } from '../ItemTypes';
import uuid from 'react-uuid'
import update from 'immutability-helper';
import { qtyReducer, validateCartonInput } from '../helper';
import { DustbinModel } from '../model/Dustbin';

const autosaveThreshold = 5 * 1000;

export const Container = memo(function Container(props) {

    const { cartons, orders, asn } = props.data.data;

    const currentAnimalType = asn.animal_type;

    const { factory_id, form_key, jquery: $, post_url, asn_id } = props.data;
    const isNewAsn = !(asn.cartons.length > 0);

    // for now only 1 Type ( style )
    const [dustbins, setDustbins] = useState([]);
    const [pickedItems, setPickedItems] = useState([]);
    const [boxes, setBoxes] = useState([]);
    const [animalType, setAnimalType] = useState(currentAnimalType);


    const [filter, setFilter] = useState({
        sku: "",
        PO: "",
        joorSONumber: "",
        doorCode: "",
        warehouseLocation: null,
        clientName: "", // Buyer
        doorLabel: "",
        colourway: "",
        name: "", // Style name
        animalType: ""
    });

    /** toDO - zrobić mapowanie i w pętle */
    const nowy = {
        sku: {label: 'Sku', value: ""},
        PO: {label: 'Po', value: ""},
        joorSONumber: {label: 'SO number', value: ""},
        doorCode: {label: 'Door code', value: ""},
        warehouseLocation: {label: 'Sku', value: null},
        clientName: "",
        doorLabel: "",
        colourway: "",
        name: ""
    }


    const [loadingMsg, setLoadingMsg] = useState("");
    const [autosaveStatus, setAutosaveStatus] = useState({message: "Asn is loading...", status: 2});
    const [cartonOptions, setCartonOptions] = useState([]);
    const [uidsToRename, setUidsToRename] = useState([]);
    const [packingListDate, setPackingListDate] = useState("");
    const [packingListNumber, setPackingListNumber] = useState("")
    const [isFirstCost, setIsFirstCost] = useState("0")
    const [totals, setTotals] = useState({
        cartons: !isNewAsn ? asn.cartons.length : 0,
        units: 0,
        value: 0
    })
    const [to, setTo] = useState(0);
    let pending = false;

    if(!isNewAsn) {
        useEffect(function () {
            setPackingListDate(asn.packing_list_date || "");
            setPackingListNumber(asn.packing_list_number || "");
            setIsFirstCost(asn.is_first_cost || "0");

            let restoredDustbins = [];
            let restoredPickedItems = [];

            asn.cartons.forEach(cartonItem => {
                const newDustbin = new DustbinModel(cartonItem.cartonId, [ItemTypes.STYLE]);
                let doorLabel; // dirty way, sorry - couldn't find better solution
                let warehouseLocation;
                let itemsInCartonCount = 0;

                newDustbin.doorCode = cartonItem.doorCode;
                newDustbin.gross_weight = cartonItem.gross_weight;
                newDustbin.net_weight = cartonItem.net_weight;
                newDustbin.dimensions = cartonItem.dimensions; // TODO dodaj w BO dla tej fabryki i spradz czy sie dobrze zanzacza
                newDustbin.suffix = cartonItem.suffix;
                newDustbin.joorSONumber = cartonItem.joorSONumber;
                newDustbin.PO = cartonItem.PO;
                newDustbin.suffixDisabled = true;

                // --------------------------------------

                cartonItem.items.forEach(productItem => {

                    // get data from Left item search by ID
                    // get data from TrueSorce is better solution
                    const leftItem = orders.find(item => item.id === productItem.id);
                    if(!leftItem) {
                        return;
                    }
                    const newProduct = {
                        id: productItem.id,
                        cartonBox: cartonItem.cartonId,
                        joorSONumber: cartonItem.joorSONumber,
                        sku: productItem.sku,
                        PO: productItem.PO,
                        sizes: productItem.sizes,
                        name: leftItem.name,
                        clientName: leftItem.clientName,
                        orderType: leftItem.orderType,
                        unit_selling_price: leftItem.unit_selling_price,
                        warehouseLocation: leftItem.warehouseLocation
                    }

                    doorLabel = leftItem.doorLabel // grap field to use outside - dirty way, but not harmful
                    warehouseLocation = leftItem.warehouseLocation
                    restoredPickedItems.push(newProduct);

                    itemsInCartonCount += qtyReducer(productItem.sizes);
                })

                // add extra fields taken from first product / the same for all other products in carton
                newDustbin.orderType = restoredPickedItems[0].orderType;
                newDustbin.toDoorLabel = doorLabel;
                newDustbin.warehouseLocation = warehouseLocation;
                newDustbin.isEmpty = false;
                newDustbin.qty = itemsInCartonCount; // TODO - add qty here
                restoredDustbins.push(newDustbin);
            })

            setPickedItems(prevState => {
                return [...prevState, ...restoredPickedItems];
            })

            setDustbins(update(dustbins, {
                $push: restoredDustbins
            }));
        }, []);
    }

    useEffect(function () {
        setBoxes(orders); // props.data
        setCartonOptions(cartons);

        isNewAsn && handleNewDustbin();
    }, []);

    useEffect(() => {
        setAutosaveStatus({message: 'Asn is about to save...', status: 2});
        clearTimeout(to)
        if(!pending) {
            setTo(setTimeout(() => {
                pending = true;
                setAutosaveStatus({message: 'Asn is now saving...', status: 2});
                let resultObject = {
                    cartons: prepareCartonsForSaveAction(),
                    packing_list_number: packingListNumber,
                    packing_list_date: packingListDate,
                    is_first_cost: isFirstCost,
                    factory_id: factory_id,
                    form_key: form_key,
                };

                $.ajax({
                    type: "POST",
                    url: post_url,
                    data: resultObject,
                    dataType: 'json'
                })
                .done(function (response, status) {
                    setUidsToRename(Object.entries(response.cartonsData).filter(elem => elem[0] !== elem[1]['unique_carton_id']))
                    setAutosaveStatus(response)
                })
                .error(function (response, status) {
                    setAutosaveStatus(response.responseJSON)
                })
                .always(function () {
                    pending = false
                });
            }, autosaveThreshold))
        }
    }, [dustbins, pickedItems, packingListDate, packingListNumber, isFirstCost])

    useEffect(() => {
        const updatedState = update(totals, {
                ['cartons']: { $set: dustbins.length }
            })
        setTotals(updatedState);
    }, [dustbins])

    const isMounted = useRef(false);

    useEffect(() => {
        if (isMounted.current) {
            if(pickedItems.length === 0){
                setAnimalType('');
            }
        } else {
            isMounted.current = true;
        }
    }, [pickedItems])

    useEffect(() => {
        let dustbinsToUpdateTheirQuantity = {}
        const spec = {};

        if(pickedItems.length === 1) {
            // lock warehouseLocation
            filterBy(pickedItems[0].warehouseLocation, 'warehouseLocation')
        }

        if(pickedItems.length > 0) {
            pickedItems.forEach(item => {
                 const thisQty = parseInt(qtyReducer(item.sizes));
                 if(!dustbinsToUpdateTheirQuantity.hasOwnProperty(item.cartonBox)) {
                     dustbinsToUpdateTheirQuantity[item.cartonBox] = thisQty;
                 } else {
                     dustbinsToUpdateTheirQuantity[item.cartonBox] += thisQty;
                 }
            })
            dustbins.forEach((dustbin) => {
                if(!dustbinsToUpdateTheirQuantity.hasOwnProperty(dustbin.uid)) {
                    return;
                }
                const index = dustbins.indexOf(dustbin);
                spec[index] = {
                    qty: {$set: dustbinsToUpdateTheirQuantity[dustbin.uid]}
                }
            })
            setDustbins(update(dustbins, spec));
        }
    },[pickedItems])

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

        if(qty === 0) {
            filterBy(null, 'warehouseLocation')
        }

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
        const currentAnimalType = `${result.cites}${result.fish_wildlife}`;

        setAnimalType(currentAnimalType);

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
        // -- warehouseLocation VAT/NOVAT
        //
        const targetDustbin = dustbins.find(bin => bin.uid === cartonBox);
        if(!validateCartonInput(targetDustbin, {
            doorCode: result.doorCode,
            orderType: result.orderType,
            joorSONumber: result.joorSONumber,
            PO: result.PO,
            warehouseLocation: result.warehouseLocation
        })) {
            return; // Abort!
        }

        if(targetDustbin.isEmpty) {

            const index = dustbins.indexOf(targetDustbin);
            const updatedDustbin = update(dustbins, {
                [index]: {
                    isEmpty: {$set: false},
                    orderType: {$set: result.orderType},
                    joorSONumber: {$set: result.joorSONumber},
                    doorCode: {$set: result.doorCode},
                    PO: {$set: result.PO},
                    toDoorLabel: {$set: result.doorLabel},
                    warehouseLocation: {$set: result.warehouseLocation}
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
                colourway: result.colourway,
                warehouseLocation: result.warehouseLocation,
                cites: result.cites,
                fish_wildlife: result.fish_wildlife
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
            warehouseLocation: result.warehouseLocation,
            colourway: result.colourway,
            cites: result.cites,
            fish_wildlife: result.fish_wildlife
        }
        setBoxes(prevState => {
            prevState[index] = updateSource;
            return [...prevState]
        });

    }, [boxes, pickedItems, dustbins]);

    function handleNewDustbin() {
        const newDustbin = new DustbinModel(uuid(), [ItemTypes.STYLE]);
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
                    PO: { $set: null},
                    warehouseLocation: { $set: null},
                    qty: { $set: 0 }
                }
            }))
        }

        const item = pickedItems.find(item => item.id === id && item.cartonBox === cartonBox);
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

    function prepareCartonsForSaveAction() {
        const data = [];
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
                data.push(binData)
            }
        })
        return data;
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

    function filterBy(value, field = '') {
        setFilter(prevState => ({
            ...prevState,
            [field]: value
        }));
    }

    return <div className="container" style={{ position: 'relative', color: '#212529', fontSize: '15px'}}>
        <div className={'row d-flex justify-content-between top-panel'}>
            <div className="col col-5 card filters">
                    <form onSubmit={ev => { ev.preventDefault()}}>
                        <h4>Filter by:</h4>
                        <div className="row">
                            <FilterField label={'Sku'} onFilter={(ev) => filterBy(ev.target.value, 'sku')} value={filter.sku} isFirst={true}/>
                            <FilterField label={'Po'} onFilter={(ev) => filterBy(ev.target.value, 'PO')} value={filter.PO}/>
                        </div>
                        <div className='row'>
                            <FilterField label={'So number'} onFilter={(ev) => filterBy(ev.target.value, 'joorSONumber')} value={filter.joorSONumber} isFirst={true}/>
                            <FilterField label={'Door code'} onFilter={(ev) => filterBy(ev.target.value, 'doorCode')} value={filter.doorCode}/>
                        </div>
                        <div className="row">
                            <FilterField label={'Buyer'} onFilter={(ev) => filterBy(ev.target.value, 'clientName')} value={filter.clientName} isFirst={true}/>
                            <FilterField label={'Door label'} onFilter={(ev) => filterBy(ev.target.value, 'doorLabel')} value={filter.doorLabel}/>
                        </div>
                        <div className="row">
                            <FilterField label={'Colourway'} onFilter={(ev) => filterBy(ev.target.value, 'colourway')} value={filter.colourway} isFirst={true}/>
                            <FilterField label={'Style name'} onFilter={(ev) => filterBy(ev.target.value, 'name')} value={filter.name}/>
                        </div>
                        <div className='row'>
                            <div className="col-12">
                                <h4 style={{ marginBottom: '0', marginTop: '5px'}}>{
                                    filter.warehouseLocation !== null && (() => {
                                        if (filter.warehouseLocation === "") {
                                            return `warehouseLocation: \<empty value\>`
                                        } else {
                                            return `warehouseLocation: ${filter.warehouseLocation}`
                                        }
                                    })()
                                }</h4>
                            </div>
                        </div>
                    </form>
                </div>
            <div className="col col-7">
                <div className="row">
                    <div className="col card totals">
                        <h4>Totals</h4>
                        <span className='label'>cartons:</span>{totals.cartons}<br/>
                        <span className='label'>units:</span>{totals.units} pcs<br/>
                        <span className='label'>value:</span>{totals.value} &euro;<br/>
                    </div>
                    <div className="col card footer">
                        <form onSubmit={ev => { ev.preventDefault()}}>
                            <div className="input-group mb-3">
                                <div className="input-group-prepend">
                                    <span className="input-group-text">Packing List Number</span>
                                </div>
                                <input type="text" className={'form-control'} onChange={ev => {setPackingListNumber(ev.target.value)}} value={packingListNumber}></input>
                            </div>
                            <div className="input-group mb-3">
                                <div className="input-group-prepend">
                                    <span className="input-group-text">Packing List Date</span>
                                </div>
                                <input type="date" className={'form-control'} onChange={ev => {setPackingListDate(ev.target.value)}} value={packingListDate}></input>
                            </div>
                            <div className="input-group mb-3">
                                <div className="input-group-prepend">
                                    <span className="input-group-text">Is First Cost</span>
                                </div>
                                <select className={'form-control'} onChange={ev => {setIsFirstCost(ev.target.value)}} value={isFirstCost}>
                                    <option key={"0"} value={"0"}>No</option>
                                    <option key={"1"} value={"1"}>Yes</option>
                                </select>
                            </div>
                        </form>
                        <DocumentStatus status={autosaveStatus} />
                    </div>
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
                                   colourway,
                                   id,
                                   sizes,
                                   clientName,
                                   joorSONumber,
                                   orderType,
                                   unit_selling_price,
                                    cites,
                                    fish_wildlife,
                                   warehouseLocation } = box;

                            const thisItemAnimalType = `${cites}${fish_wildlife}`;

                            const isVisible = PO.includes(filter.PO)
                                    && sku.includes(filter.sku)
                                    && joorSONumber.includes(filter.joorSONumber)
                                    && doorCode.includes(filter.doorCode)
                                    && doorLabel.includes(filter.doorLabel.toUpperCase())
                                    && name.includes(filter.name.toUpperCase())
                                    && (colourway && colourway.includes(filter.colourway.toUpperCase()))
                                    && clientName.includes(filter.clientName.toUpperCase())
                                    && (warehouseLocation === filter.warehouseLocation || filter.warehouseLocation === null )
                                    && animalType === thisItemAnimalType || animalType === ''

                            return (<Box name={name}
                                 hidden={!isVisible}
                                 type={type}
                                 sku={sku}
                                 doorLabel={doorLabel}
                                 doorCode={doorCode}
                                 colourway={colourway}
                                 id={id}
                                 PO={PO}
                                 sizes={sizes}
                                 clientName={clientName}
                                 joorSONumber={joorSONumber}
                                 orderType={orderType}
                                 unit_selling_price={unit_selling_price}
                                 warehouseLocation={warehouseLocation}
                                 boxAfter={false}
                                 key={index}/>)}) : <p>There is no items for search criteria".</p> }
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
                    <div style={{
                        height: 'calc(100vh - 85px)',
                        position: 'sticky',
                        top: '50px',
                        overflow: 'auto',
                        marginBottom: '1rem',
                        paddingRight: '1rem',
                        paddingTop: '10px',
                        paddingBottom: '10px'
                    }}>
                        <div className={'mb-3'} style={{ height: '100%'}}>
                            {dustbins.map(({
                                        accepts,
                                        uid,
                                        toDoorLabel,
                                        orderType,
                                        joorSONumber,
                                        PO,
                                        warehouseLocation,
                                        isEmpty,
                                        gross_weight,
                                        net_weight,
                                        dimensions,
                                        suffixDisabled,
                                        qty,
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
                                                isEmpty={isEmpty}
                                                joorSONumber={joorSONumber}
                                                warehouseLocation={warehouseLocation}
                                                setCartonInfo={handleSetCartonInfo}
                                                readOnly={false}
                                                info={info}
                                                uidsToRename={uidsToRename}
                                                suffixDisabled={suffixDisabled}
                                                cartonOptions={cartonOptions}
                                                assignedItems={pickedItems && pickedItems.filter(item => item.cartonBox === uid)}
                                                qty={qty}
                                                key={uid}/>
                            })}
                        </div>
                    </div>
                </div>
            </div>
        { loadingMsg ? <span className={'mask'} style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: '#fff', opacity: 0.4 }}>
            <span>{loadingMsg}</span>
        </span> : ''}
        </div>
});

