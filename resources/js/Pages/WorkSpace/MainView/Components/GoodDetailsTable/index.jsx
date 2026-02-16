import React from 'react';
import { observer } from 'mobx-react-lite';
import { GoodDetailsTableHeader } from './Components/GoodDetailsTableHeader';
import { GoodDetailsTableBody } from './Components/GoodDetailsTableBody';
import './styles.css';

export const GoodDetailsTable = observer(({ goodDetails, dates, workSpaceSettings, handleOpenNotes }) => {
    return (
        <div className="table-container">
            <table className="sticky-table">
                <GoodDetailsTableHeader
                    dates={dates}
                    workSpaceSettings={workSpaceSettings}
                />
                <GoodDetailsTableBody
                    dates={dates}
                    goodDetails={goodDetails}
                    handleOpenNotes={handleOpenNotes}
                />
            </table>
        </div>
    );
});
