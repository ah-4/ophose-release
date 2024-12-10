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
            if(oph._ == 'a' && oph.href && !oph.default) {
                oph.onclick = (e) => {
                    e.preventDefault();
                    route.go(oph.href);
                }
            }

            if(oph.cooldown) {
                let originalWatch = oph.watch;
                if(!originalWatch) {
                    dev.error('The cooldown attribute requires a watch attribute');
                    return oph;
                }
                let fakeLive = live(originalWatch);
                oph.watch = fakeLive;
                let lastCall = Date.now();
                watch(fakeLive, (value) => {
                    lastCall = Date.now();
                    setTimeout(() => {
                        if(Date.now() - lastCall > oph.cooldown) {
                            originalWatch.value = value;
                        }
                    }, oph.cooldown);
                });
            }
            
            return oph;
        });
    }

}