<style>
    .print-sheet {
        display: none;
    }

    .print-clinic-header {
        display: grid;
        grid-template-columns: 0.65in 1fr;
        align-items: center;
        gap: 0.15in;
        padding-bottom: 0.12in;
        border-bottom: 1px solid #222;
        text-align: center;
    }

    .print-clinic-logo {
        width: 0.58in;
        height: 0.58in;
        object-fit: contain;
    }

    .print-clinic-header h1 {
        margin: 0;
        font-size: 13pt;
        line-height: 1.15;
        text-transform: uppercase;
    }

    .print-clinic-header p {
        margin: 0.02in 0 0;
        font-size: 7pt;
        line-height: 1.15;
    }

    .print-document-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 0.1in 0;
    }

    .print-document-heading h2 {
        margin: 0;
        font-size: 10pt;
        letter-spacing: 0.08em;
    }

    .print-document-heading span {
        font-size: 8pt;
        font-weight: 700;
    }

    .print-patient-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        column-gap: 0.16in;
        row-gap: 0.055in;
        padding: 0.08in 0;
        border-top: 1px solid #777;
        border-bottom: 1px solid #777;
    }

    .print-field {
        display: flex;
        gap: 0.04in;
        min-width: 0;
        font-size: 7.5pt;
        line-height: 1.25;
    }

    .print-field span {
        flex: 0 0 auto;
        font-weight: 700;
    }

    .print-field strong {
        min-width: 0;
        overflow-wrap: anywhere;
        font-weight: 400;
    }

    .medical-record-patient-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr 0.9fr;
        gap: 0.08in 0.18in;
        width: 84%;
        margin: 0.18in 0 0.22in;
    }

    .medical-record-patient-column {
        display: flex;
        flex-direction: column;
        gap: 0.075in;
    }

    .medical-record-patient-column .print-field {
        align-items: baseline;
        font-size: 7.5pt;
        line-height: 1.25;
    }

    .medical-record-patient-column .print-field strong {
        flex: 1 1 auto;
        min-height: 0.12in;
        border-bottom: 1px solid #555;
    }

    .medical-record-narrative {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.14in;
        width: 92%;
    }

    .medical-record-narrative h3 {
        margin: 0 0 0.025in;
        font-size: 7.5pt;
        text-transform: none;
    }

    .medical-record-narrative p {
        min-height: 0.12in;
        margin: 0;
        font-size: 7.5pt;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .medical-record-narrative .print-follow-up {
        gap: 0.06in;
        font-size: 7.5pt;
    }

    .print-narrative {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.09in 0.16in;
        margin-top: 0.1in;
    }

    .print-narrative h3,
    .print-prescription-section h3 {
        margin: 0 0 0.025in;
        font-size: 7.5pt;
        text-transform: uppercase;
    }

    .print-narrative p,
    .print-prescription-section p {
        margin: 0;
        font-size: 7.5pt;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .print-follow-up {
        display: flex;
        gap: 0.05in;
        font-size: 7.5pt;
    }

    .print-prescription-section {
        margin-top: 0.12in;
    }

    .print-prescription-section table {
        width: 100%;
        border-collapse: collapse;
        font-size: 7pt;
    }

    .print-prescription-section th,
    .print-prescription-section td {
        padding: 0.045in 0.06in;
        border: 1px solid #888;
        text-align: left;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .print-signature-row {
        display: flex;
        justify-content: space-between;
        gap: 0.2in;
        margin-top: 0.16in;
        font-size: 7.5pt;
    }

    @page {
        size: letter portrait;
        margin: 0.32in;
    }

    @media print {
        html,
        body {
            width: 100%;
            min-height: 0 !important;
            margin: 0 !important;
            background: #fff !important;
            color: #111 !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        body *:not(:has(.print-sheet)):not(.print-sheet):not(.print-sheet *) {
            display: none !important;
        }

        body * {
            visibility: hidden !important;
        }

        body :has(.print-sheet) {
            display: block !important;
            position: static !important;
            inset: auto !important;
            width: auto !important;
            height: auto !important;
            min-height: 0 !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            transform: none !important;
        }

        .print-sheet,
        .print-sheet * {
            visibility: visible !important;
        }

        .print-sheet {
            position: relative !important;
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #111 !important;
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 8pt !important;
            box-shadow: none !important;
            border: 0 !important;
        }

        .print-sheet * {
            box-shadow: none !important;
        }

        .print-clinic-header,
        .print-document-heading,
        .print-patient-grid,
        .print-narrative,
        .medical-record-patient-grid,
        .medical-record-patient-column,
        .medical-record-narrative > div,
        .print-prescription-section,
        .print-signature-row {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
