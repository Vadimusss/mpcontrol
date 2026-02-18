import React from 'react';
import '../../../styles.css';

export const ToolTip = ({ tooltipData }) => {
    return (
        <div className="global-tooltip" style={{
            position: 'fixed',
            left: `${tooltipData.x}px`,
            top: `${tooltipData.y}px`,
            zIndex: 99999,
            background: '#ffffff',
            color: '#000000',
            padding: '6px 10px',
            borderRadius: '4px',
            width: 'fit-content',
            whiteSpace: 'normal',
            fontSize: '14px',
            lineHeight: '1.4',
            pointerEvents: 'none',
            boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
            border: '1px solid #d9d9d9',
        }}>
            {tooltipData.text}
        </div>
    );
};