class OphosePlugin extends Ophose.Plugin {

    constructor() {
        super('OphosePlugin');

        this.useRender((oph) => {
            if(oph._.includes('.')) {
                let tagAndClass = oph._.split('.');
                let tag = tagAndClass.shift();
                let classes = tagAndClass.join(' ');
                oph._ = tag;
                oph.className = ' ' + classes;
            }
            if(oph._ == 'a' && oph.href) {
                oph.onclick = (e) => {
                    e.preventDefault();
                    route.go(oph.href);
                }
            }
            return oph;
        });
    }

}