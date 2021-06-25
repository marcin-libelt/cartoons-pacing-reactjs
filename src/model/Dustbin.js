function DustbinModel(uuid, accepts) {

    this.uid = uuid;
    this.accepts = accepts;

    this.doorCode = null; // unique for dustbin
    this.orderType = null; // unique for dustbin
    this.joorSONumber = null; // unique for dustbin
    this.PO = null; // unique for dustbin
    this.toDoorLabel = null;
    this.gross_weight = '';
    this.net_weight = '';
    this.dimensions = '';
    this.suffix = '';
    this.isEmpty = true;
}



export { DustbinModel };