import Injector from 'lib/Injector';
import FocusPointField from 'components/FocusPointField';
import FocusPointPicker from 'components/FocusPointPicker';

const registerComponents = () => {
  Injector.component.register('FocusPointField', FocusPointField);
  Injector.component.register('FocusPointPicker', FocusPointPicker);
};

export default registerComponents;
