<style>
    /* Scroll vertical y horizontal para "Todas las letras de pago" */
    div.schedule-scroll {
        max-height: 80vh;
        overflow: auto;
    }

    div.schedule-scroll::-webkit-scrollbar {
        height: 12px;
    }

    div.schedule-scroll > table > thead > tr > th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #fff;
        box-shadow: inset 0 -2px 0 #ddd;
    }

    @media print {
        div.schedule-scroll {
            max-height: none;
            overflow: visible;
        }
    }
</style>
