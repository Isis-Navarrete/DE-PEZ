import Stack from '@elementor/ui/Stack';
import Typography from '@elementor/ui/Typography';
import { __ } from '@wordpress/i18n';
import { Setting } from './setting';
import { useSettingsContext } from './use-settings-context';
import { Spinner } from '@wordpress/components';

export const Seo = () => {
	const {
		themeSettings: { skip_link: skipLink },
		updateSetting,
		isLoading,
	} = useSettingsContext();

	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<Stack gap={ 2 }>
			<Typography
				variant="subtitle2">
				{ __( 'These settings affect how search engines and assistive technologies interact with your website.', 'hello-biz' ) }
			</Typography>
			<Setting
				value={ skipLink }
				label={ __( 'Disable skip links', 'hello-biz' ) }
				onSwitchClick={ () => updateSetting( 'skip_link', ! skipLink ) }
				description={ __( 'What it does: Removes the "Skip to content" link that helps screen reader users and keyboard navigators jump directly to the main content.', 'hello-biz' ) }
				code={ '<a class="skip-link screen-reader-text" href="#content">Skip to content</a>' }
				tip={ __( 'Tip: If you use an accessibility plugin that adds a "skip to content" link, disable this option to prevent duplications.', 'hello-biz' ) }
			/>
		</Stack>
	);
};
