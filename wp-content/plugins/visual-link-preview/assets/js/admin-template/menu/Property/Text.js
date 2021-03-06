import React from 'react';

const PropertyText = (props) => {
    return (
        <input
            className="vlp-template-property-input"
            type="text"
            value={props.value}
            onChange={(e) => props.onValueChange(e.target.value)}
        />
    );
}

export default PropertyText;