import React, {PropTypes, Component, cloneElement} from 'react';
import classNames from 'classnames';
import { inject } from 'lib/Injector';


class FocusPointField extends Component {
  constructor(props) {
    super(props);

    this.state = {
      FocusX: props.data ? props.data.X : 0,
      FocusY: props.data ? props.data.Y : 0
    };
    this.handleFocusChange = this.handleFocusChange.bind(this);
  }

  handleFocusChange({focusX, focusY}) {
    this.setState({
      FocusX: focusX,
      FocusY: focusY
    });

    const {onAutofill, name} = this.props;

    if (typeof onAutofill === 'function') {
      onAutofill(`${name}X`, focusX);
      onAutofill(`${name}Y`, focusY);
    }
  }

  handleFieldChange(childIndex, event) {
    const {children, name} = this.props;
    const childName = children[childIndex].props.name;
    let value = parseFloat(event.target.value);

    if (Number.isNaN(value)) {
      value = 0;
    }
    if (childName === `${name}X`) {
      this.setState({
        FocusX: value
      });
    } else {
      this.setState({
        FocusY: value
      });
    }
  }

  renderChildren(children, isDebug) {
    if (isDebug) {
      return children.map((child, key) => (
        cloneElement(child, {
          // overload the children change handler
          onChange: (e) => this.handleFieldChange(key, e),
          key,
          value: child.props.value || 0
        }, child.props.children)
      ))
    }

    return (children);
  }

  render() {
    const {FocusPointPicker, FieldGroup, children, readOnly} = this.props;
    const {showDebug, tooltip, previewUrl, previewWidth, previewHeight} = this.props.data;
    const {FocusX, FocusY} = this.state;

    const newProps = {
      ...this.props,
      className: classNames('focuspoint-field', {'focuspoint-field--debug': showDebug})
    };

    return (
      <FieldGroup {...newProps}>
        <div className="focuspoint-field__image-wrapper">
          <FocusPointPicker
            className="focuspoint-field__picker"
            imageUrl={previewUrl}
            focusX={FocusX}
            focusY={FocusY}
            width={Math.ceil(previewWidth * 0.5)}
            height={Math.ceil(previewHeight * 0.5)}
            tooltip={tooltip}
            readOnly={readOnly}
            onChange={this.handleFocusChange}
          />
        </div>
        {this.renderChildren(children, showDebug)}
      </FieldGroup>
    )
  }
}

FocusPointField.defaultProps = {
  extraClass: '',
  value: ''
};

FocusPointField.propTypes = {
  extraClass: React.PropTypes.string,
  id: React.PropTypes.string,
  name: React.PropTypes.string.isRequired,
  children: PropTypes.array.isRequired,
  onAutofill: PropTypes.func,
  readOnly: React.PropTypes.bool
};

export { FocusPointField as Component };

export default inject(
  ['FieldGroup', 'FocusPointPicker']
)(FocusPointField);
//export default fieldHolder(FocusPointField);
