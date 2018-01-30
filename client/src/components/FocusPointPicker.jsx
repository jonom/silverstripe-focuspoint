import React, {PropTypes, Component} from 'react';
import classNames from 'classnames';

class FocusPointPicker extends Component {
  constructor(props) {
    super(props);
    this.state = {
      focusX: props.focusX || 0,
      focusY: props.focusY || 0
    };
    this.handleClick = this.handleClick.bind(this);
  }

  handleClick(event) {
    if (this.props.readOnly) {
      return;
    }
    let x = event.nativeEvent.offsetX;
    let y = event.nativeEvent.offsetY;

    const {width, height} = this.props;

    if (typeof this.props.onChange === 'function') {
      this.props.onChange({
        focusX: (x * 2 / width) - 1,
        focusY: (y * 2 / height) - 1
      });
    }
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      focusX: nextProps.focusX || 0,
      focusY: nextProps.focusY || 0
    });
  }

  render() {
    const {focusX, focusY} = this.state;
    const {width, height, imageUrl, tooltip, className} = this.props;
    const style = {
      left: Math.round((focusX + 1) * 0.5 * width),
      top: Math.round((focusY + 1) * 0.5 * height),
      width: width * 2,
      height: height * 2
    };
    return (
      <div className={classNames(className, "focuspoint-picker")} title={tooltip}>
        <img className="focuspoint-picker__image"
             src={imageUrl}
             width={width}
             height={height}
        />
        <span className="focuspoint-picker__gradient" style={style}/>
        <span className="focuspoint-picker__overlay" onClick={this.handleClick}/>
      </div>
    )
  }
}

FocusPointPicker.defaultProps = {
  imageUrl: '',
  width: 0,
  height: 0,
  tooltip: '',
  onChange: null
};


export default FocusPointPicker;
