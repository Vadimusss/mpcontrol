import React from 'react';

export const Colgroup = ({ dates }) => {
    return (
        <colgroup>
            <col style={{ width: '52px' }} />
            <col style={{ width: '90px' }} />
            <col style={{ width: '200px' }} />
            <col style={{ width: '150px' }} />
            <col style={{ width: '110px' }} />
            <col style={{ width: '90px' }} />
            {dates.map((date) => <col key={`date-${date}`} style={{ width: '56px' }} />)}
            <col style={{ width: '80px' }} />
            {[...Array(4).keys()].map((number) => <col key={`col-${number}`} style={{ width: '70px' }} />)}
            {[...Array(2).keys()].map((number) => <col key={`col-${number}`} style={{ width: '76px' }} />)}
            {[...Array(2).keys()].map((number) => <col key={`col-${number}`} style={{ width: '94px' }} />)}
            {[...Array(35).keys()].map((number) => <col key={`col-${number}`} style={{ width: '70px' }} />)}
        </colgroup>
    );
};