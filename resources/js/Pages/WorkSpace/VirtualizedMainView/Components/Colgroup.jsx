import React from 'react';

export const Colgroup = ({ dates }) => {
    return (
        <colgroup>
            <col style={{ width: '52px' }} />
            <col style={{ width: '60px' }} />
            <col style={{ width: '200px' }} />
            <col style={{ width: '150px' }} />
            <col style={{ width: '64px' }} />
            <col style={{ width: '40px' }} />
            {dates.map((date) => <col key={`date-${date}`} style={{ width: '40px' }} />)}
            {[...Array(12).keys()].map((number) => <col key={`col-${number}`} style={{ width: '50px' }} />)}
        </colgroup>
    );
};