BX.ready(function () {
    const link = window.location.pathname;
    if(link.split("/").includes("deal")){
        const ID = link.split("/")[4]
        const main = document.getElementById('toolbar_deal_details_'+ID);
        var openSlider =  async function () {
            BX.SidePanel.Instance.open("/local/modules/mws.sed.fdoc/UI/?ID="+ID);
        }
        if(main){
            main.prepend(
                BX.create("span", {
                    attrs: {
                        className: "ui-btn ui-btn-danger-light",
                    },
                    dataset: {

                    },
                    events: {
                        click: BX.proxy(openSlider, this),
                    },
                    text: "Документы для подписания клиентом через сервис F.doc",
                })
            )
        }

    }

});