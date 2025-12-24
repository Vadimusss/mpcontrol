import React from 'react';

export const Colgroup = ({ dates }) => {
    return (
        <colgroup>
            <col style={{ width: '52px' }} />
            <col style={{ width: '80px' }} />
            <col style={{ width: '200px' }} />
            <col style={{ width: '150px' }} />
            <col style={{ width: '84px' }} />
            {dates.map((date) => <col key={`date-${date}`} style={{ width: '46px' }} />)}
            {[...Array(12).keys()].map((number) => <col key={`col-${number}`} style={{ width: '60px' }} />)}
        </colgroup>
    );
};