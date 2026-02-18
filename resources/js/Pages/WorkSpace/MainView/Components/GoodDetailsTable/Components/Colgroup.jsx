import React from 'react';

export const Colgroup = ({ dates }) => {
    return (
        <colgroup>
            <col style={{ width: '180px' }} />
            {dates.map((date) => <col key={`date-${date}`} style={{ width: '56px' }} />)}
            <col style={{ width: '80px' }} />
            <col style={{ width: '40px' }} />
            {[...Array(5).keys()].map((number) => <col key={`col-${number}`} style={{ width: '60px' }} />)}
        </colgroup>
    );
};